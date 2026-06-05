<?php

use App\Console\Commands\PrepareTestDatabase;

test('it builds statements for the configured testing database and application user', function () {
    $command = new PrepareTestDatabase;

    expect($command->preparationStatements('wedruj_z_nami_testing', 'user', '%'))->toBe([
        'CREATE DATABASE IF NOT EXISTS `wedruj_z_nami_testing`',
        "GRANT ALL PRIVILEGES ON `wedruj_z_nami_testing`.* TO 'user'@'%'",
    ]);
});

test('it rejects unsafe database identifiers', function () {
    $command = new PrepareTestDatabase;

    $command->preparationStatements('wedruj-z-nami-testing', 'user', '%');
})->throws(InvalidArgumentException::class, 'Invalid database identifier [wedruj-z-nami-testing].');

test('phpunit uses the dedicated testing database', function () {
    $phpunitXml = file_get_contents(dirname(__DIR__, 2).'/phpunit.xml');

    expect($phpunitXml)->toContain('<env name="DB_CONNECTION" value="mariadb"/>')
        ->and($phpunitXml)->toContain('<env name="DB_DATABASE" value="wedruj_z_nami_testing"/>')
        ->and($phpunitXml)->not->toContain('<env name="DB_DATABASE" value="wedruj_z_nami"/>');
});

test('composer prepares the testing database before running the suite', function () {
    $composer = json_decode(file_get_contents(dirname(__DIR__, 2).'/composer.json'), true);

    expect($composer['scripts']['test'])->toBe([
        '@php artisan config:clear --ansi',
        '@php artisan test:prepare-database --ansi',
        '@lint:check',
        '@php artisan test',
    ]);
});
