<?php

namespace Pasax\SecureMonitor\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Pasax\SecureMonitor\Database\Seeders\PasaxDefaultSettingsSeeder;
use Pasax\SecureMonitor\Models\PasaxAllowedIp;
use Pasax\SecureMonitor\Models\PasaxBlockedIp;
use Pasax\SecureMonitor\Models\PasaxIpUnblockRequest;
use Pasax\SecureMonitor\Models\PasaxLog;
use Pasax\SecureMonitor\Models\PasaxLoginAttempt;
use Pasax\SecureMonitor\Models\PasaxSetting;
use Pasax\SecureMonitor\Models\PasaxTrustedIp;
use Pasax\SecureMonitor\Models\PasaxUserLogin;
use Pasax\SecureMonitor\Services\PasaxDashboardUrlService;
use Pasax\SecureMonitor\Services\PasaxVersionCheckService;
use Pasax\SecureMonitor\Support\PasaxDashboardSecret;
use Throwable;

/**
 * Perintah deployment otomatis untuk server VPS / production.
 *
 * Menjalankan migrasi khusus tabel Pasax, memastikan secret dashboard di .env,
 * menanam seeder pengaturan bawaan tanpa duplikasi data, dan menyegarkan cache.
 * Aman dijalankan berulang kali (idempotent) tanpa menyentuh tabel data aplikasi host.
 */
class PasaxDeployCommand extends Command
{
    protected $signature = 'pasax:deploy
                            {--force : Jalankan proses deployment tanpa prompt interaktif}
                            {--reset-settings : Setel ulang seluruh isi tabel pasax_settings ke nilai bawaan}
                            {--reset-tables : Kosongkan (truncate/delete) hanya tabel-tabel khusus Pasax tanpa menyentuh tabel host}
                            {--force-secret : Buat ulang PASAX_DASHBOARD_SECRET meskipun sudah terisi di .env}
                            {--no-migrate : Lewati eksekusi migrasi database}
                            {--no-cache-clear : Lewati pembersihan cache framework}
                            {--env-path= : Lokasi berkas .env tujuan (default: base_path(\'.env\'))}';

    protected $description = 'Otomasi deployment VPS untuk Pasax: memastikan secret dashboard, migrasi database, seeder pengaturan tanpa duplikasi data, dan penyegaran cache';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <bg=blue;fg=white;options=bold> PASAX DEPLOY (VPS / PRODUCTION) </>');
        $this->newLine();

        $force = (bool) $this->option('force');

        // 1. Secret Dashboard (PASAX_DASHBOARD_SECRET)
        $this->comment('1/5. Memeriksa dan memastikan PASAX_DASHBOARD_SECRET pada .env...');
        $envPath = (string) ($this->option('env-path') ?: base_path('.env'));
        $forceSecret = (bool) $this->option('force-secret');

        if (File::exists($envPath)) {
            $beforeSecret = PasaxDashboardSecret::readFromEnvFile($envPath);
            $secretResult = PasaxDashboardSecret::ensureInEnvFile($envPath, $forceSecret, true);

            if ($secretResult['generated']) {
                $verb = $beforeSecret === null ? 'dibuat' : 'dibuat ulang (--force-secret)';
                $this->info("  ✓ PASAX_DASHBOARD_SECRET berhasil {$verb} di {$envPath}.");
            } else {
                $this->info('  ✓ PASAX_DASHBOARD_SECRET sudah terisi dan aman (tidak ditimpa).');
            }
        } else {
            $this->warn("  ⚠ Berkas .env tidak ditemukan di {$envPath}. Langkah secret dilewati.");
        }

        $examplePath = base_path('.env.example');
        if (File::exists($examplePath)) {
            PasaxDashboardSecret::ensureInEnvFile($examplePath, false, false);
        }
        $this->newLine();

        // 2. Reset Tabel Khusus Pasax jika diminta (--reset-tables)
        $resetTables = (bool) $this->option('reset-tables');
        if ($resetTables) {
            $this->comment('2/5. Menyetel ulang tabel-tabel khusus Pasax (--reset-tables)...');

            $confirmed = $force ||
                ! $this->input->isInteractive() ||
                $this->confirm(
                    'PERINGATAN: Opsi --reset-tables akan mengosongkan seluruh tabel khusus Pasax (logs, blocked_ips, settings, dll). Tabel aplikasi host (users, dll) TIDAK disentuh. Lanjutkan?',
                    false
                );

            if (! $confirmed) {
                $this->warn('  Penyetelan ulang tabel dibatalkan oleh pengguna.');
            } else {
                $clearedCount = $this->resetDedicatedPasaxTables();
                $this->info("  ✓ {$clearedCount} tabel khusus Pasax berhasil dikosongkan (tabel aplikasi host tetap 100% aman).");
            }
            $this->newLine();
        } else {
            $this->line('  (2/5. Riwayat log dan tabel khusus Pasax dipertahankan).');
            $this->newLine();
        }

        // 3. Migrasi Database Khusus Pasax
        if (! $this->option('no-migrate')) {
            $this->comment('3/5. Menyinkronkan dan menerapkan migrasi database Pasax...');

            try {
                $this->callSilent('vendor:publish', [
                    '--tag' => 'pasax-migrations',
                    '--force' => true,
                ]);
            } catch (Throwable) {
                // Abaikan jika vendor:publish migrations tidak dapat dipanggil langsung
            }

            $this->call('migrate', ['--force' => true]);
            $this->info('  ✓ Migrasi database telah disinkronkan dan diterapkan.');
        } else {
            $this->line('  (3/5. Migrasi database dilewati via --no-migrate).');
        }
        $this->newLine();

        // 4. Seeder Pengaturan Bawaan (PasaxDefaultSettingsSeeder)
        $this->comment('4/5. Menanam / menyinkronkan pengaturan keamanan bawaan ke database...');
        $resetSettings = (bool) $this->option('reset-settings') || $resetTables;

        if ($resetSettings && ! $resetTables) {
            try {
                $settingsTable = (new PasaxSetting)->getTable();
                if (Schema::hasTable($settingsTable)) {
                    DB::table($settingsTable)->delete();
                }
            } catch (Throwable) {
                // Abaikan jika tabel belum ada
            }
        }

        try {
            $seeder = app(PasaxDefaultSettingsSeeder::class);
            $seededKeys = $seeder->run($resetSettings);

            if ($resetSettings) {
                $this->info('  ✓ Pengaturan keamanan bawaan berhasil disetel ulang ke nilai default ('.count($seededKeys).' kunci).');
            } elseif ($seededKeys === []) {
                $this->info('  ✓ Seluruh pengaturan keamanan bawaan sudah ada di database (tidak ada duplikasi data).');
            } else {
                $this->info('  ✓ '.count($seededKeys).' pengaturan keamanan baru berhasil ditanam ke database.');
            }
        } catch (Throwable $e) {
            $this->warn('  ⚠ Gagal menanam pengaturan bawaan: '.$e->getMessage());
        }
        $this->newLine();

        // 5. Bersihkan Cache Framework
        if (! $this->option('no-cache-clear')) {
            $this->comment('5/5. Membersihkan cache framework dan menyegarkan konfigurasi...');
            try {
                $this->callSilent('config:clear');
                $this->callSilent('route:clear');
                $this->callSilent('view:clear');
            } catch (Throwable) {
                // Fallback jika perintah cache tidak tersedia di lingkungan pengujian
            }
            Cache::forget(PasaxVersionCheckService::CACHE_KEY);
            $this->info('  ✓ Cache framework (config, route, view) berhasil disegarkan.');
        } else {
            $this->line('  (5/5. Pembersihan cache dilewati via --no-cache-clear).');
        }
        $this->newLine();

        // Status Selesai
        $this->info('  🎉 Deployment Pasax selesai dengan aman dan siap digunakan!');

        if (config('pasax.dashboard.enabled', false)) {
            try {
                $urlService = app(PasaxDashboardUrlService::class);
                $entryUrl = $urlService->entryUrl();
                $this->line("  Dashboard URL: <info>{$entryUrl}</info>");
            } catch (Throwable) {
                $prefix = ltrim((string) config('pasax.dashboard.prefix', 'pasax'), '/');
                $this->line("  Dashboard URL: <info>/{$prefix}</info>");
            }
        }
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Dapatkan daftar nama tabel database yang khusus dimiliki oleh paket Pasax.
     * SAMA SEKALI TIDAK mencakup tabel pengguna (users) atau tabel aplikasi host lainnya.
     *
     * @return array<int, string>
     */
    public static function getDedicatedPasaxTables(): array
    {
        $tables = [
            (new PasaxSetting)->getTable(),
            (new PasaxAllowedIp)->getTable(),
            (new PasaxTrustedIp)->getTable(),
            (new PasaxUserLogin)->getTable(),
            (new PasaxIpUnblockRequest)->getTable(),
            (new PasaxLoginAttempt)->getTable(),
            (new PasaxLog)->getTable(),
            (new PasaxBlockedIp)->getTable(),
        ];

        $usersTable = (string) config('pasax.table_names.users', 'users');

        return array_values(array_filter(
            array_unique($tables),
            fn (string $table): bool => $table !== '' &&
                $table !== $usersTable &&
                ! str_ends_with($table, 'users')
        ));
    }

    /**
     * Kosongkan hanya tabel-tabel khusus paket Pasax.
     */
    protected function resetDedicatedPasaxTables(): int
    {
        $cleared = 0;
        $tables = self::getDedicatedPasaxTables();

        Schema::disableForeignKeyConstraints();

        try {
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                    $cleared++;
                }
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        return $cleared;
    }
}
