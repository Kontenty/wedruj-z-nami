<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Executes read-only diagnostics and deployment maintenance tasks
 * on behalf of an authenticated administrator.
 *
 * Safety rules:
 * - Only whitelisted actions can run (validated by DeploymentActionRequest).
 * - User output is truncated and never contains exception traces or
 *   log file contents; full details go to the laravel log with a
 *   reference id plus a server-side audit entry.
 */
class DeploymentRunner
{
    private const OUTPUT_LIMIT = 6000;

    /**
     * @return array<string, array{title: string, description: string, destructive: bool}>
     */
    public static function descriptors(): array
    {
        return [
            'diagnostics' => [
                'title' => 'Diagnostyka',
                'description' => 'Sprawdzenie tylko do odczytu: PHP, ścieżki, uprawnienia, baza danych.',
                'destructive' => false,
            ],
            'storage_health' => [
                'title' => 'Kondycja dysków',
                'description' => 'Próbny zapis, odczyt i usunięcie pliku na dyskach lokalnych i skonfigurowanych.',
                'destructive' => false,
            ],
            'storage_link' => [
                'title' => 'Dowiązanie storage',
                'description' => 'Utworzenie lub naprawa publicznego dowiązania storage (storage:link).',
                'destructive' => true,
            ],
            'config_clear' => [
                'title' => 'Czyszczenie konfiguracji',
                'description' => 'Usunięcie cache konfiguracji, aby wczytane zostały nowe wartości .env.',
                'destructive' => false,
            ],
            'cache_clear' => [
                'title' => 'Czyszczenie cache',
                'description' => 'Czyszczenie cache aplikacji, tras, widoków, zdarzeń i konfiguracji.',
                'destructive' => false,
            ],
            'database_check' => [
                'title' => 'Połączenie z bazą',
                'description' => 'Test skonfigurowanego połączenia z bazą zapytaniem tylko do odczytu.',
                'destructive' => false,
            ],
            'migrate' => [
                'title' => 'Migracje bazy',
                'description' => 'Uruchomienie oczekujących migracji z opcją force.',
                'destructive' => true,
            ],
            'media_regenerate' => [
                'title' => 'Regeneracja konwersji mediów',
                'description' => 'Regeneracja wszystkich pochodnych plików mediów z opcją force.',
                'destructive' => true,
            ],
            'media_regenerate_missing' => [
                'title' => 'Regeneracja brakujących konwersji',
                'description' => 'Regeneracja tylko tych konwersji, których plików pochodnych brakuje.',
                'destructive' => true,
            ],
            'optimize' => [
                'title' => 'Optymalizacja i cache',
                'description' => 'Zapisanie cache konfiguracji, tras i widoków.',
                'destructive' => true,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function actionNames(): array
    {
        return array_keys(self::descriptors());
    }

    public function __construct(private User $user, private string $ip) {}

    /**
     * @return array{success: bool, output: string}
     */
    public function run(string $action): array
    {
        if (! in_array($action, self::actionNames(), true)) {
            return ['success' => false, 'output' => 'Nieznana akcja.'];
        }

        try {
            $output = match ($action) {
                'diagnostics' => $this->diagnostics(),
                'storage_health' => $this->storageHealth(),
                'storage_link' => $this->storageLink(),
                'config_clear' => $this->runArtisan('config:clear'),
                'cache_clear' => $this->runArtisanMany(['cache:clear', 'route:clear', 'view:clear', 'event:clear', 'config:clear']),
                'database_check' => $this->databaseCheck(),
                'migrate' => $this->runArtisan('migrate', ['--force' => true]),
                'media_regenerate' => $this->runArtisan('media-library:regenerate', ['--force' => true]),
                'media_regenerate_missing' => $this->runArtisan('media-library:regenerate', ['--force' => true, '--only-missing' => true]),
                'optimize' => $this->runArtisanMany(['config:cache', 'route:cache', 'view:cache']),
            };

            $result = ['success' => true, 'output' => $this->truncate($output)];
            $this->audit($action, true, $result['output']);

            return $result;
        } catch (Throwable $exception) {
            return $this->failure($action, $exception);
        }
    }

    public function diagnostics(): string
    {
        $lines = [
            '=== Środowisko ===',
            'PHP: '.PHP_VERSION.' ('.PHP_OS.')',
            'Czas serwera: '.now()->format('Y-m-d H:i:s T'),
            'Środowisko: '.config('app.env'),
            'Laravel: '.Application::VERSION,
            'Debug: '.(config('app.debug') ? 'WŁĄCZONY' : 'wyłączony'),
            'Tryb konserwacji: '.(app()->isDownForMaintenance() ? 'WŁĄCZONY' : 'wyłączony'),
            'Cache konfiguracji: '.(app()->configurationIsCached() ? 'włączony' : 'wyłączony'),
            'Cache tras: '.(app()->routesAreCached() ? 'włączony' : 'wyłączony'),
        ];

        if (app()->environment('production') && config('app.debug')) {
            $lines[] = 'OSTRZEŻENIE: APP_DEBUG jest włączony na produkcji.';
        }

        foreach (['public' => 'app/public', 'private' => 'app/private'] as $label => $relative) {
            $lines[] = "storage/{$label}: ".$this->describePath(storage_path($relative));
        }
        $lines[] = 'public/storage: '.$this->describePath(public_path('storage'));

        try {
            $startedAt = microtime(true);
            DB::select('select 1');
            $connection = DB::connection();
            $lines[] = 'Baza: połączono ('.round((microtime(true) - $startedAt) * 1000, 1).' ms, '.$connection->getDriverName().')';
            if (Schema::hasTable('media')) {
                $lines[] = 'Tabela media: '.DB::table('media')->count().' wierszy';
            }
        } catch (Throwable $exception) {
            $lines[] = 'Baza: BŁĄD - '.$this->safeMessage($exception);
        }

        return implode("\n", $lines)."\n";
    }

    private function storageHealth(): string
    {
        $lines = [$this->diagnostics(), '--- Próby zapisu lokalnego ---'];

        foreach (
            [
                'storage/app/public' => storage_path('app/public'),
                'storage/app/private' => storage_path('app/private'),
                'storage/framework/cache' => storage_path('framework/cache'),
                'storage/framework/sessions' => storage_path('framework/sessions'),
                'storage/framework/views' => storage_path('framework/views'),
                'storage/logs' => storage_path('logs'),
            ] as $label => $path
        ) {
            $lines[] = $this->directoryProbe($label, $path);
        }

        $lines[] = '--- Próby zapisu na dyskach ---';
        foreach ($this->configuredDiskNames() as $disk) {
            $lines[] = $this->diskProbe($disk);
        }

        return implode("\n", $lines)."\n";
    }

    private function storageLink(): string
    {
        foreach ([storage_path('app/public'), storage_path('app/private'), storage_path('logs')] as $path) {
            if (! is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }

        return $this->diagnostics().$this->runArtisan('storage:link');
    }

    private function databaseCheck(): string
    {
        DB::select('select 1');
        $connection = DB::connection();

        $lines = [
            $this->diagnostics(),
            'Połączenie działa.',
            'Sterownik: '.$connection->getDriverName(),
            'Baza: '.($connection->getDatabaseName() ?: 'nieznana'),
        ];

        try {
            $lines[] = 'Tabel: '.count(DB::select('show tables'));
        } catch (Throwable $exception) {
            $lines[] = 'Liczenie tabel pominięto: '.$this->safeMessage($exception);
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function runArtisan(string $command, array $parameters = []): string
    {
        $exitCode = Artisan::call($command, $parameters);
        $output = "--- {$command} (exit {$exitCode}) ---\n".Artisan::output();

        if ($exitCode !== 0) {
            throw new DeploymentActionException("Polecenie {$command} zakończyło się kodem {$exitCode}.");
        }

        return $output;
    }

    /**
     * @param  array<int, string>  $commands
     */
    private function runArtisanMany(array $commands): string
    {
        $output = $this->diagnostics();

        foreach ($commands as $command) {
            $output .= $this->runArtisan($command);
        }

        return $output;
    }

    /**
     * @return array<int, string>
     */
    private function configuredDiskNames(): array
    {
        $names = [
            config('filesystems.default'),
            config('media-library.disk_name'),
        ];

        return array_values(array_unique(array_filter(
            $names,
            fn (mixed $name): bool => is_string($name) && $name !== '',
        )));
    }

    private function directoryProbe(string $label, string $path): string
    {
        if (! is_dir($path) || ! is_writable($path)) {
            return "[niepowodzenie] {$label}: brak katalogu lub brak zapisu ({$path})";
        }

        $probe = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'.deployment-health-'.Str::random(8);
        $payload = 'deployment-health';

        try {
            if (@file_put_contents($probe, $payload, LOCK_EX) !== strlen($payload)) {
                return "[niepowodzenie] {$label}: nie można zapisać próby";
            }
            if (@file_get_contents($probe) !== $payload) {
                return "[niepowodzenie] {$label}: nie można odczytać próby";
            }

            return "[ok] {$label}: zapis/odczyt/usunięcie powiodło się";
        } finally {
            if (file_exists($probe)) {
                @unlink($probe);
            }
        }
    }

    private function diskProbe(string $disk): string
    {
        $probe = '.deployment-health-'.Str::random(8).'.txt';

        try {
            $filesystem = Storage::disk($disk);
            if (! $filesystem->put($probe, 'deployment-health')) {
                return "[niepowodzenie] dysk {$disk}: nie można zapisać próby";
            }
            if ($filesystem->get($probe) !== 'deployment-health') {
                return "[niepowodzenie] dysk {$disk}: nie można odczytać próby";
            }
            $filesystem->delete($probe);

            return "[ok] dysk {$disk}: zapis/odczyt/usunięcie powiodło się";
        } catch (Throwable $exception) {
            try {
                Storage::disk($disk)->delete($probe);
            } catch (Throwable) {
            }

            return "[niepowodzenie] dysk {$disk}: ".$this->safeMessage($exception);
        }
    }

    private function describePath(string $path): string
    {
        if (is_link($path)) {
            return 'dowiązanie -> '.(@readlink($path) ?: '?');
        }
        if (! file_exists($path)) {
            return 'brak';
        }

        return (is_dir($path) ? 'katalog' : 'plik').', '.(is_writable($path) ? 'zapisywalny' : 'NIEZAPISYWALNY');
    }

    /**
     * @return array{success: false, output: string}
     */
    private function failure(string $action, Throwable $exception): array
    {
        $reference = Str::upper(Str::random(8));

        Log::error('Deployment action failed', [
            'reference' => $reference,
            'action' => $action,
            'user_id' => $this->user->id,
            'ip' => $this->ip,
            'exception' => $exception,
        ]);

        $output = "Akcja nie powiodła się. Numer referencyjny: {$reference}. Szczegóły zapisano w logach serwera.";
        if ($exception instanceof DeploymentActionException) {
            $output .= "\n".$exception->getMessage();
        }
        $this->audit($action, false, "reference={$reference}");

        return ['success' => false, 'output' => $output];
    }

    private function safeMessage(Throwable $exception): string
    {
        return Str::limit($exception->getMessage(), 200);
    }

    private function truncate(string $output): string
    {
        return mb_substr(trim($output), 0, self::OUTPUT_LIMIT) ?: 'Polecenie zakończone.';
    }

    private function audit(string $action, bool $success, string $output): void
    {
        $line = sprintf(
            "[%s] user=%d ip=%s action=%s success=%d\n%s\n%s\n",
            now()->format('Y-m-d H:i:s T'),
            $this->user->id,
            $this->ip,
            $action,
            $success ? 1 : 0,
            str_repeat('-', 60),
            mb_substr($output, 0, self::OUTPUT_LIMIT)
        );

        @file_put_contents(storage_path('logs/deployment.log'), $line, FILE_APPEND);
    }
}
