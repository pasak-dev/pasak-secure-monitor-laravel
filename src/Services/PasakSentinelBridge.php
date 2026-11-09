<?php

namespace Internal\Pasak\Services;

use Throwable;

/**
 * High-performance IPC bridge connecting the PHP/Laravel application to the
 * local Pasak Sentinel host daemon via Unix Domain Socket (/run/pasak/agent.sock).
 *
 * Dispatches instant IP ban instructions to the Linux kernel firewall (nftables/iptables),
 * achieving sub-millisecond (< 0.2ms) packet drops directly at the OS level before
 * malicious traffic can exhaust PHP-FPM workers or web server threads.
 *
 * Fully fail-safe: if the daemon is stopped or the socket is unavailable, requests
 * fail silently and fall back to application-layer database blocking without disrupting user requests.
 */
class PasakSentinelBridge
{
    protected string $socketPath;
    protected bool $enabled;
    protected float $timeoutSeconds;

    public function __construct(?string $socketPath = null, ?bool $enabled = null, float $timeoutSeconds = 0.5)
    {
        $this->socketPath = $socketPath ?? (string) config('pasak.sentinel.socket', env('PASAK_SENTINEL_SOCKET', '/run/pasak/agent.sock'));
        $this->enabled = $enabled ?? (bool) config('pasak.sentinel.enabled', env('PASAK_SENTINEL_ENABLED', true));
        $this->timeoutSeconds = $timeoutSeconds;

        // Fallback to /tmp/pasak/agent.sock if primary socket does not exist but /tmp does (common in macOS / local dev)
        if (! file_exists($this->socketPath) && file_exists('/tmp/pasak/agent.sock')) {
            $this->socketPath = '/tmp/pasak/agent.sock';
        }
    }

    /**
     * Check if the Pasak Sentinel daemon socket is reachable.
     */
    public function isAvailable(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        return file_exists($this->socketPath) && $this->ping();
    }

    /**
     * Send a PING probe to the Sentinel daemon.
     */
    public function ping(): bool
    {
        $response = $this->send(['action' => 'PING']);

        return isset($response['status']) && $response['status'] === 'ok' && ($response['message'] ?? '') === 'PONG';
    }

    /**
     * Query Sentinel daemon status and active kernel firewall rules.
     */
    public function status(): ?array
    {
        $response = $this->send(['action' => 'STATUS']);

        if (isset($response['status']) && $response['status'] === 'ok') {
            return $response['data'] ?? [];
        }

        return null;
    }

    /**
     * Delegate immediate IP blocking to the Linux kernel firewall via Sentinel daemon.
     */
    public function blockIp(string $ip, ?string $reason = null, int $durationMinutes = 1440): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        $response = $this->send([
            'action' => 'BLOCK_IP',
            'ip' => $ip,
            'reason' => $reason ?? 'ANOMALOUS_PAYLOAD_DETECTED',
            'duration_minutes' => $durationMinutes,
        ]);

        return isset($response['status']) && $response['status'] === 'ok';
    }

    /**
     * Instruct the Sentinel daemon to lift a kernel firewall drop rule.
     */
    public function unblockIp(string $ip): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        $response = $this->send([
            'action' => 'UNBLOCK_IP',
            'ip' => $ip,
        ]);

        return isset($response['status']) && $response['status'] === 'ok';
    }

    /**
     * Transmit a JSON payload over the Unix Domain Socket and return the parsed response.
     */
    protected function send(array $payload): ?array
    {
        if (! $this->enabled || ! file_exists($this->socketPath)) {
            return null;
        }

        $fp = null;

        try {
            $errno = 0;
            $errstr = '';
            $fp = @stream_socket_client(
                'unix://' . $this->socketPath,
                $errno,
                $errstr,
                $this->timeoutSeconds
            );

            if (! $fp) {
                return null;
            }

            stream_set_timeout($fp, (int) $this->timeoutSeconds, (int) (($this->timeoutSeconds - (int) $this->timeoutSeconds) * 1000000));

            $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES) . "\n";
            @fwrite($fp, $encoded);

            $raw = @fgets($fp);
            if (! $raw) {
                return null;
            }

            $decoded = json_decode(trim($raw), true);

            return is_array($decoded) ? $decoded : null;
        } catch (Throwable) {
            return null;
        } finally {
            if ($fp && is_resource($fp)) {
                @fclose($fp);
            }
        }
    }

    public function getSocketPath(): string
    {
        return $this->socketPath;
    }
}
