<?php

namespace Pasax\SecureMonitor\Console\Commands;

use Illuminate\Console\Command;
use Pasax\SecureMonitor\Models\PasaxBlockedIp;
use Pasax\SecureMonitor\Services\PasaxService;

/**
 * Escape hatch so an administrator can never be permanently locked out of the
 * panel when their own IP gets blocked by the security monitor.
 */
class PasaxUnblockIpCommand extends Command
{
    protected $signature = 'pasax:unblock-ip
                            {ip? : Alamat IP yang akan dibuka blokirnya}
                            {--all : Buka blokir seluruh IP}
                            {--list : Tampilkan daftar IP yang sedang diblokir}';

    protected $description = 'Buka blokir alamat IP yang diblokir oleh Security Monitor';

    public function handle(PasaxService $service): int
    {
        if ($this->option('list') || (! $this->argument('ip') && ! $this->option('all'))) {
            return $this->listBlocks();
        }

        if ($this->option('all')) {
            $blocks = PasaxBlockedIp::query()->active()->get();

            if (! $this->confirm("Buka blokir untuk {$blocks->count()} IP?", true)) {
                return self::SUCCESS;
            }

            foreach ($blocks as $block) {
                $service->unblock($block);
            }

            $this->info("{$blocks->count()} blokir IP telah dibuka.");

            return self::SUCCESS;
        }

        $ip = (string) $this->argument('ip');
        $block = PasaxBlockedIp::query()->where('ip_address', $ip)->first();

        if ($block === null) {
            $this->warn("IP {$ip} tidak ada dalam daftar blokir.");

            return self::SUCCESS;
        }

        $service->unblock($block);

        $this->info("Blokir untuk IP {$ip} telah dibuka.");

        return self::SUCCESS;
    }

    protected function listBlocks(): int
    {
        $blocks = PasaxBlockedIp::query()
            ->active()
            ->orderByDesc('blocked_at')
            ->get(['ip_address', 'source', 'reason', 'expires_at', 'hit_count']);

        if ($blocks->isEmpty()) {
            $this->info('Tidak ada IP yang sedang diblokir.');

            return self::SUCCESS;
        }

        $this->table(
            ['IP', 'Sumber', 'Percobaan', 'Kedaluwarsa', 'Alasan'],
            $blocks->map(fn (PasaxBlockedIp $block) => [
                $block->ip_address,
                $block->source,
                $block->hit_count,
                $block->expires_at?->diffForHumans() ?? 'Permanen',
                mb_strimwidth((string) $block->reason, 0, 60, '…'),
            ])->all()
        );

        return self::SUCCESS;
    }
}
