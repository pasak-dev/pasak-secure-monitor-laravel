<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Pasax\SecureMonitor\Console\Commands\PasaxDeployCommand;
use Pasax\SecureMonitor\Models\PasaxBlockedIp;
use Pasax\SecureMonitor\Models\PasaxLog;
use Pasax\SecureMonitor\Models\PasaxSetting;
use Pasax\SecureMonitor\Support\PasaxDashboardSecret;

function pasaxDeployTemporaryEnv(): string
{
    $dir = sys_get_temp_dir().'/pasax-deploy-'.uniqid('', true);
    File::ensureDirectoryExists($dir);

    return $dir.'/.env';
}

test('pasax:deploy terdaftar sebagai perintah artisan resmi', function () {
    expect(array_keys(Artisan::all()))->toContain('pasax:deploy');
});

test(
    'pasax:deploy memastikan PASAX_DASHBOARD_SECRET terisi di .env tanpa menimpa rahasia lama',
    function () {
        $envPath = pasaxDeployTemporaryEnv();

        try {
            File::put(
                $envPath,
                "APP_ENV=production\nPASAX_DASHBOARD_SECRET=\n",
            );

            $this->artisan('pasax:deploy', [
                '--env-path' => $envPath,
                '--force' => true,
            ])->assertSuccessful();

            $secret = PasaxDashboardSecret::readFromEnvFile($envPath);
            expect($secret)
                ->toBeString()
                ->and(strlen((string) $secret))
                ->toBe(PasaxDashboardSecret::LENGTH);

            // Jalankan ulang: rahasia yang sama TIDAK boleh ditimpa
            $this->artisan('pasax:deploy', [
                '--env-path' => $envPath,
                '--force' => true,
            ])->assertSuccessful();

            expect(PasaxDashboardSecret::readFromEnvFile($envPath))->toBe(
                $secret,
            );

            // Dengan --force-secret: rahasia baru dibuat
            $this->artisan('pasax:deploy', [
                '--env-path' => $envPath,
                '--force-secret' => true,
                '--force' => true,
            ])->assertSuccessful();

            $newSecret = PasaxDashboardSecret::readFromEnvFile($envPath);
            expect($newSecret)
                ->not->toBe($secret)
                ->and(strlen((string) $newSecret))
                ->toBe(PasaxDashboardSecret::LENGTH);
        } finally {
            File::delete($envPath);
        }
    },
);

test(
    'pasax:deploy menanam pengaturan default secara idempotent tanpa duplikasi data saat diulang',
    function () {
        $envPath = pasaxDeployTemporaryEnv();

        try {
            File::put(
                $envPath,
                "APP_ENV=production\nPASAX_DASHBOARD_SECRET=\n",
            );

            // Jalankan pertama kali
            $this->artisan('pasax:deploy', [
                '--env-path' => $envPath,
                '--force' => true,
            ])->assertSuccessful();

            $initialCount = PasaxSetting::query()->count();
            expect($initialCount)->toBeGreaterThan(0);

            // Jalankan kedua kali di server: count harus sama persis (tidak ada duplikasi data)
            $this->artisan('pasax:deploy', [
                '--env-path' => $envPath,
                '--force' => true,
            ])->assertSuccessful();

            expect(PasaxSetting::query()->count())->toBe($initialCount);
        } finally {
            File::delete($envPath);
        }
    },
);

test(
    'pasax:deploy dengan --reset-settings menyetel ulang pengaturan tanpa duplikasi data',
    function () {
        $envPath = pasaxDeployTemporaryEnv();

        try {
            File::put(
                $envPath,
                "APP_ENV=production\nPASAX_DASHBOARD_SECRET=\n",
            );

            $this->artisan('pasax:deploy', [
                '--env-path' => $envPath,
                '--force' => true,
            ])->assertSuccessful();

            // Modifikasi salah satu setting
            PasaxSetting::query()
                ->where('key', 'auto_block.threshold')
                ->update(['value' => serialize(99)]);
            expect(
                unserialize(
                    PasaxSetting::query()
                        ->where('key', 'auto_block.threshold')
                        ->value('value'),
                ),
            )->toBe(99);

            // Jalankan deploy dengan --reset-settings
            $this->artisan('pasax:deploy', [
                '--env-path' => $envPath,
                '--reset-settings' => true,
                '--force' => true,
            ])->assertSuccessful();

            // Nilai kembali ke default (3) dan tidak ada duplikasi data
            expect(
                unserialize(
                    PasaxSetting::query()
                        ->where('key', 'auto_block.threshold')
                        ->value('value'),
                ),
            )->toBe(3);
        } finally {
            File::delete($envPath);
        }
    },
);

test(
    'pasax:deploy dengan --reset-tables hanya mengosongkan tabel khusus Pasax dan tidak menyentuh tabel host users',
    function () {
        $envPath = pasaxDeployTemporaryEnv();

        try {
            File::put(
                $envPath,
                "APP_ENV=production\nPASAX_DASHBOARD_SECRET=\n",
            );

            // Pastikan tabel users terisi data host aplikasi
            $usersTable = (string) config('pasax.table_names.users', 'users');
            if (Schema::hasTable($usersTable)) {
                DB::table($usersTable)->insertOrIgnore([
                    'id' => 9999,
                    'name' => 'Host Application User',
                    'email' => 'hostuser@domain.test',
                    'password' => bcrypt('secret'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Isi log dan blocked ip di tabel khusus Pasax
            PasaxBlockedIp::query()->create([
                'ip_address' => '203.0.113.50',
                'reason' => 'Test attack',
                'is_active' => true,
            ]);

            PasaxLog::query()->create([
                'ip_address' => '203.0.113.50',
                'threat_level' => 'high',
                'event_type' => 'xss',
            ]);

            expect(PasaxBlockedIp::query()->count())->toBeGreaterThan(0);
            expect(PasaxLog::query()->count())->toBeGreaterThan(0);

            // Jalankan deploy dengan --reset-tables
            $this->artisan('pasax:deploy', [
                '--env-path' => $envPath,
                '--reset-tables' => true,
                '--force' => true,
            ])->assertSuccessful();

            // Tabel Pasax harus ter-reset dan settings terisi ulang oleh seeder bawaan
            expect(PasaxBlockedIp::query()->count())->toBe(0);
            expect(PasaxLog::query()->count())->toBe(0);
            expect(PasaxSetting::query()->count())->toBeGreaterThan(0);

            // Tabel pengguna aplikasi host (users) HARUS tetap utuh!
            if (Schema::hasTable($usersTable)) {
                $hostUser = DB::table($usersTable)
                    ->where('email', 'hostuser@domain.test')
                    ->first();
                expect($hostUser)->not->toBeNull();
            }
        } finally {
            File::delete($envPath);
        }
    },
);

test(
    'getDedicatedPasaxTables mengecualikan tabel users dan tabel non-pasax',
    function () {
        $dedicated = PasaxDeployCommand::getDedicatedPasaxTables();
        $usersTable = (string) config('pasax.table_names.users', 'users');

        expect($dedicated)
            ->not->toContain($usersTable)
            ->not->toContain('users')
            ->toContain((new PasaxSetting)->getTable());
    },
);
