<?php

namespace Pasax\SecureMonitor\Console\Commands;

use Illuminate\Console\Command;
use Pasax\SecureMonitor\Models\PasaxLog;

class PasaxPruneSecurityLogsCommand extends Command
{
    protected $signature = 'pasax:prune-logs {--days= : Override the configured retention period}';

    protected $description = 'Hapus log keamanan yang lebih lama dari masa retensi yang dikonfigurasi';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('pasax.retention_days', 90));

        if ($days <= 0) {
            $this->info('Retensi log keamanan dinonaktifkan, tidak ada log yang dihapus.');

            return self::SUCCESS;
        }

        $deleted = PasaxLog::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("{$deleted} log keamanan lebih lama dari {$days} hari berhasil dihapus.");

        return self::SUCCESS;
    }
}
