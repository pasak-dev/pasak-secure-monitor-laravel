<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Pasax\SecureMonitor\Support\PasaxDashboardSecret;

function pasaxTemporaryEnvFile(): string
{
    $dir = sys_get_temp_dir().'/pasax-secret-'.uniqid('', true);

    File::ensureDirectoryExists($dir);

    return $dir.'/.env';
}

test('pasax:secret terdaftar sebagai perintah artisan', function () {
    expect(array_keys(Artisan::all()))->toContain('pasax:secret');
});

test('pasax:secret membuat rahasia saat baris masih kosong', function () {
    $path = pasaxTemporaryEnvFile();

    try {
        File::put($path, "APP_NAME=Demo\nPASAX_DASHBOARD_SECRET=\n");

        $this->artisan('pasax:secret', ['--path' => $path])
            ->assertSuccessful();

        $secret = PasaxDashboardSecret::readFromEnvFile($path);

        expect($secret)
            ->toBeString()
            ->and(strlen((string) $secret))
            ->toBe(PasaxDashboardSecret::LENGTH);
    } finally {
        File::delete($path);
    }
});

test('pasax:secret tidak menimpa rahasia yang sudah ada kecuali --force', function () {
    $path = pasaxTemporaryEnvFile();

    try {
        File::put($path, "PASAX_DASHBOARD_SECRET=existing-secret-value\n");

        $this->artisan('pasax:secret', ['--path' => $path])
            ->assertSuccessful();

        expect(PasaxDashboardSecret::readFromEnvFile($path))
            ->toBe('existing-secret-value');

        $this->artisan('pasax:secret', [
            '--path' => $path,
            '--force' => true,
        ])->assertSuccessful();

        $regenerated = PasaxDashboardSecret::readFromEnvFile($path);

        expect($regenerated)
            ->not->toBe('existing-secret-value')
            ->and(strlen((string) $regenerated))
            ->toBe(PasaxDashboardSecret::LENGTH);
    } finally {
        File::delete($path);
    }
});

test('pasax:install membuat secret hanya pada .env', function () {
    $envPath = base_path('.env');
    $examplePath = base_path('.env.example');
    $originalEnv = File::exists($envPath) ? File::get($envPath) : null;
    $originalExample = File::exists($examplePath)
        ? File::get($examplePath)
        : null;

    try {
        File::put($envPath, "APP_NAME=Demo\n");
        File::put($examplePath, "APP_NAME=Demo\nPASAX_DASHBOARD_SECRET=legacy-leaked-value\n");

        $this->artisan('pasax:install', [
            '--force' => true,
            '--without-nginx' => true,
            '--without-htaccess' => true,
            '--without-views' => true,
            '--without-user-trait' => true,
            '--without-middleware' => true,
            '--without-routes' => true,
            '--without-api' => true,
            '--without-web' => true,
        ])->assertSuccessful();

        $secret = PasaxDashboardSecret::readFromEnvFile($envPath);

        expect($secret)
            ->toBeString()
            ->and(strlen((string) $secret))
            ->toBe(PasaxDashboardSecret::LENGTH)
            ->and(File::get($examplePath))
            ->toContain('PASAX_DASHBOARD_SECRET=')
            ->not->toContain('legacy-leaked-value')
            ->and(PasaxDashboardSecret::readFromEnvFile($examplePath))
            ->toBeNull();
    } finally {
        if ($originalEnv !== null) {
            File::put($envPath, $originalEnv);
        }

        if ($originalExample !== null) {
            File::put($examplePath, $originalExample);
        }
    }
});

test('rahasia contoh tidak ikut ditulis ke .env.example', function () {
    $dir = dirname(pasaxTemporaryEnvFile());

    try {
        $env = $dir.'/.env';
        $example = $dir.'/.env.example';

        File::put($env, "PASAX_DASHBOARD_SECRET=\n");
        File::put($example, "PASAX_DASHBOARD_SECRET=\n");

        PasaxDashboardSecret::ensureInEnvFile($env);
        PasaxDashboardSecret::ensureInEnvFile($example, false, false);

        expect(PasaxDashboardSecret::readFromEnvFile($env))->toBeString()
            ->and(PasaxDashboardSecret::readFromEnvFile($example))->toBeNull();
    } finally {
        File::deleteDirectory($dir);
    }
});
