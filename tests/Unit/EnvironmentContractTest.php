<?php

use Tests\TestCase;

uses(TestCase::class);

/**
 * @return array<int, string>
 */
function configEnvironmentKeys(): array
{
    $keys = [];

    foreach (glob(base_path('config/*.php')) as $filePath) {
        $contents = file_get_contents($filePath);

        preg_match_all("/env\\(['\"]([A-Z0-9_]+)['\"]/", $contents, $matches);

        foreach ($matches[1] as $key) {
            $keys[] = $key;
        }
    }

    $keys = array_values(array_unique($keys));
    sort($keys);

    return $keys;
}

/**
 * @return array<int, string>
 */
function exampleEnvironmentKeys(): array
{
    $keys = [];
    $lines = file(base_path('.env.example'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (preg_match('/^([A-Z0-9_]+)=/', $line, $matches) === 1) {
            $keys[] = $matches[1];
        }
    }

    $keys = array_values(array_unique($keys));
    sort($keys);

    return $keys;
}

test('the sample env file covers all config environment keys', function () {
    $missingKeys = array_values(array_diff(configEnvironmentKeys(), exampleEnvironmentKeys()));

    expect($missingKeys)->toBe([]);
});

test('docker compose uses the documented environment defaults', function () {
    $dockerCompose = file_get_contents(base_path('docker-compose.yml'));

    expect($dockerCompose)->toContain('DB_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-root}')
        ->toContain('DB_DATABASE: ${DB_DATABASE:-wedruj_z_nami}')
        ->toContain('DB_USER: ${DB_USER:-user}')
        ->toContain('name: ${VOLUME_NAME:-mariadb_data_wedruj_z_nami}')
        ->toContain('name: ${NETWORK_NAME:-mariadb_network_wedruj_z_nami}')
        ->toContain('restart: ${RESTART_POLICY:-unless-stopped}');
});
