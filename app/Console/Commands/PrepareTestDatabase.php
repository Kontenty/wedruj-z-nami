<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use InvalidArgumentException;
use PDO;

#[Signature('test:prepare-database')]
#[Description('Create the MariaDB testing database and grant the application user access.')]
class PrepareTestDatabase extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = config('database.default');

        if ($connection !== 'mariadb') {
            $this->warn("Skipping test database preparation for [{$connection}] connection.");

            return self::SUCCESS;
        }

        $databaseConfig = config('database.connections.mariadb');
        $database = config('database.testing_database');
        $username = $databaseConfig['username'];
        $userHost = config('database.root_host');

        try {
            $pdo = $this->rootConnection($databaseConfig);

            foreach ($this->preparationStatements($database, $username, $userHost) as $statement) {
                $pdo->exec($statement);
            }
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Testing database [{$database}] is ready.");

        return self::SUCCESS;
    }

    /**
     * @param  array{host: string, port: string, unix_socket?: string, charset: string}  $databaseConfig
     */
    protected function rootConnection(array $databaseConfig): PDO
    {
        $dsn = filled($databaseConfig['unix_socket'] ?? '')
            ? sprintf('mysql:unix_socket=%s;charset=%s', $databaseConfig['unix_socket'], $databaseConfig['charset'])
            : sprintf('mysql:host=%s;port=%s;charset=%s', $databaseConfig['host'], $databaseConfig['port'], $databaseConfig['charset']);

        return new PDO($dsn, 'root', config('database.root_password'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    /**
     * @return list<string>
     */
    public function preparationStatements(string $database, string $username, string $userHost): array
    {
        return [
            sprintf('CREATE DATABASE IF NOT EXISTS %s', $this->quoteIdentifier($database)),
            sprintf(
                'GRANT ALL PRIVILEGES ON %s.* TO %s@%s',
                $this->quoteIdentifier($database),
                $this->quoteString($username),
                $this->quoteString($userHost),
            ),
        ];
    }

    private function quoteIdentifier(string $identifier): string
    {
        if (! preg_match('/^[A-Za-z0-9_]+$/', $identifier)) {
            throw new InvalidArgumentException("Invalid database identifier [{$identifier}].");
        }

        return "`{$identifier}`";
    }

    private function quoteString(string $value): string
    {
        return "'".str_replace("'", "''", $value)."'";
    }
}
