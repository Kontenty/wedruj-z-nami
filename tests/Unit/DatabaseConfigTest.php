<?php

use Tests\TestCase;

uses(TestCase::class);

function withEnvironmentVariables(array $variables, Closure $callback): mixed
{
    $originalVariables = [];

    foreach ($variables as $name => $value) {
        $originalVariables[$name] = [
            'env' => array_key_exists($name, $_ENV) ? $_ENV[$name] : null,
            'server' => array_key_exists($name, $_SERVER) ? $_SERVER[$name] : null,
            'getenv' => getenv($name),
        ];

        if ($value === null) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);

            continue;
        }

        putenv(sprintf('%s=%s', $name, $value));
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }

    try {
        return $callback();
    } finally {
        foreach ($originalVariables as $name => $originalVariable) {
            if ($originalVariable['getenv'] === false) {
                putenv($name);
            } else {
                putenv(sprintf('%s=%s', $name, $originalVariable['getenv']));
            }

            if ($originalVariable['env'] === null) {
                unset($_ENV[$name]);
            } else {
                $_ENV[$name] = $originalVariable['env'];
            }

            if ($originalVariable['server'] === null) {
                unset($_SERVER[$name]);
            } else {
                $_SERVER[$name] = $originalVariable['server'];
            }
        }
    }
}

test('mysql connections use the new mysql environment variables', function () {
    withEnvironmentVariables([
        'DB_HOST' => 'mariadb',
        'DB_PORT' => '3307',
        'DB_DATABASE' => 'app_db',
        'DB_USER' => 'app_user',
        'DB_PASSWORD' => 'secret',
    ], function (): void {
        $config = require dirname(__DIR__, 2).'/config/database.php';

        expect($config['connections']['mysql']['host'])->toBe('mariadb');
        expect($config['connections']['mysql']['port'])->toBe('3307');
        expect($config['connections']['mysql']['database'])->toBe('app_db');
        expect($config['connections']['mysql']['username'])->toBe('app_user');
        expect($config['connections']['mysql']['password'])->toBe('secret');

        expect($config['connections']['mariadb']['host'])->toBe('mariadb');
        expect($config['connections']['mariadb']['port'])->toBe('3307');
        expect($config['connections']['mariadb']['database'])->toBe('app_db');
        expect($config['connections']['mariadb']['username'])->toBe('app_user');
        expect($config['connections']['mariadb']['password'])->toBe('secret');
    });
});
