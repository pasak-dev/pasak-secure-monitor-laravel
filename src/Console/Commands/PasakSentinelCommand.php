<?php

namespace Internal\Pasak\Console\Commands;

use Illuminate\Console\Command;
use Internal\Pasak\Services\PasakSentinelBridge;

class PasakSentinelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pasak:sentinel
                            {--ping : Test connectivity with the Sentinel daemon}
                            {--status : Inspect Sentinel status and active kernel firewall rules}
                            {--block= : Immediate kernel drop test for the specified IP address}
                            {--unblock= : Lift kernel firewall drop for the specified IP address}
                            {--reason= : Reason for manual IP drop}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Communicate directly with the local Pasak Sentinel host daemon over Unix socket';

    public function handle(PasakSentinelBridge $bridge): int
    {
        $this->info('[Pasak Sentinel IPC Diagnostics]');
        $this->line('Socket target: ' . $bridge->getSocketPath());

        if ($ipToBlock = $this->option('block')) {
            $reason = (string) ($this->option('reason') ?: 'MANUAL_ARTISAN_PROBE');
            $this->info("Transmitting kernel block command for IP: {$ipToBlock}...");
            $success = $bridge->blockIp($ipToBlock, $reason);

            if ($success) {
                $this->info("✔ Success: IP {$ipToBlock} dropped in kernel firewall via Sentinel.");
                return self::SUCCESS;
            }

            $this->error("✖ Error: Failed to drop IP {$ipToBlock} over Sentinel IPC socket.");
            return self::FAILURE;
        }

        if ($ipToUnblock = $this->option('unblock')) {
            $this->info("Transmitting kernel unblock command for IP: {$ipToUnblock}...");
            $success = $bridge->unblockIp($ipToUnblock);

            if ($success) {
                $this->info("✔ Success: IP {$ipToUnblock} unblocked in kernel firewall.");
                return self::SUCCESS;
            }

            $this->error("✖ Error: Failed to unblock IP {$ipToUnblock} over Sentinel IPC socket.");
            return self::FAILURE;
        }

        if ($this->option('ping')) {
            $this->info('Pinging Sentinel daemon...');
            if ($bridge->ping()) {
                $this->info('✔ PONG received: Sentinel daemon is active and responsive.');
                return self::SUCCESS;
            }

            $this->error('✖ PING failed: Sentinel daemon did not respond.');
            return self::FAILURE;
        }

        // Default or --status
        $this->info('Inspecting Sentinel daemon status...');
        $status = $bridge->status();

        if ($status !== null) {
            $this->info('✔ Sentinel Daemon is ONLINE');
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Firewall Driver', $status['firewall_driver'] ?? 'unknown'],
                    ['Kernel Drops Count', $status['blocked_count'] ?? 0],
                    ['Active Drops Sample', implode(', ', (array) ($status['blocked_ips'] ?? []))],
                ]
            );
            return self::SUCCESS;
        }

        $this->warn('⚠ Sentinel daemon is OFFLINE or socket is not created.');
        $this->line('Ensure `pasak-sentinel` daemon is running on the host system.');
        return self::FAILURE;
    }
}
