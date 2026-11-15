<?php

namespace Pasax\SecureMonitor\Console\Commands;

use Illuminate\Console\Command;

class PasaxCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pasax';

    /**
     * The aliases of the command.
     *
     * @var array<int, string>
     */
    protected $aliases = ['pasak'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tampilkan status Pasax Security Monitor dan panduan perintah artisan yang tersedia';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->output->writeln('');
        $this->output->writeln('  <fg=yellow;options=bold>██████╗  █████╗ ███████╗ █████╗ ██╗  ██╗</>');
        $this->output->writeln('  <fg=yellow;options=bold>██╔══██╗██╔══██╗██╔════╝██╔══██╗╚██╗██╔╝</>');
        $this->output->writeln('  <fg=yellow;options=bold>██████╔╝███████║███████╗███████║ ╚███╔╝ </>');
        $this->output->writeln('  <fg=yellow;options=bold>██╔═══╝ ██╔══██║╚════██║██╔══██║ ██╔██╗ </>');
        $this->output->writeln('  <fg=yellow;options=bold>██║     ██║  ██║███████║██║  ██║██╔╝ ██╗</>');
        $this->output->writeln('  <fg=yellow;options=bold>╚═╝     ╚═╝  ╚═╝╚══════╝╚═╝  ╚═╝╚═╝  ╚═╝</>');
        $this->output->writeln('  <fg=cyan;options=bold>PASAX DEFENSE SUITE — HEADLESS WAF & SECURITY MONITOR (v'.\Pasax\SecureMonitor\Services\PasaxService::VERSION.')</>');
        $this->output->writeln('');

        $enabled = config('pasax.enabled', true);
        $statusStr = $enabled ? '<fg=green;options=bold>ACTIVE</>' : '<fg=red;options=bold>DISABLED</>';
        $this->output->writeln("  Status Perlindungan WAF : {$statusStr}");
        $this->output->writeln("  Table Prefix            : <fg=white>".config('pasax.table_prefix', 'pasax_')."</>");
        $this->output->writeln("  Dashboard URL Path      : <fg=white>/".config('pasax.dashboard.prefix', 'pasax')."</>");
        $this->output->writeln('');
        $this->output->writeln('  <fg=yellow;options=bold>Daftar Perintah Artisan Tersedia:</>');
        $this->output->writeln('  <fg=green>php artisan pasax:install</>         - Wizard instalasi otomatis WAF & dashboard');
        $this->output->writeln('  <fg=green>php artisan pasax:deploy</>          - Jalankan migrasi dan inisialisasi env di server produksi');
        $this->output->writeln('  <fg=green>php artisan pasax:baseline</>        - Rekam SHA-256 integritas file aplikasi (FIM anti-tamper)');
        $this->output->writeln('  <fg=green>php artisan pasax:cloud</>           - Sinkronisasi telemetri & ancaman ke Cloud SOC Control Plane');
        $this->output->writeln('  <fg=green>php artisan pasax:sentinel</>        - Cek koneksi jembatan Unix IPC ke daemon Linux pasax-sentinel');
        $this->output->writeln('  <fg=green>php artisan pasax:scan-logs</>       - Pindai log akses web server secara real-time / background');
        $this->output->writeln('  <fg=green>php artisan pasax:unblock-ip</>      - Lepaskan IP atau perangkat yang terisolasi dari karantina');
        $this->output->writeln('  <fg=green>php artisan pasax:secret</>          - Buat atau rotasi secret key internal dashboard WAF');
        $this->output->writeln('  <fg=green>php artisan pasax:prune-logs</>      - Bersihkan riwayat log keamanan yang melebihi batas retensi');
        $this->output->writeln('  <fg=green>php artisan pasax:upgrade</>         - Sinkronisasi aset, konfigurasi, dan tabel ke rilis terbaru');
        $this->output->writeln('  <fg=green>php artisan pasax:uninstall</>       - Hapus tabel dan konfigurasi pasax dari aplikasi host');
        $this->output->writeln('');

        return self::SUCCESS;
    }
}
