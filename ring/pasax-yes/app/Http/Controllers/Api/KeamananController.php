<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pasax\SecureMonitor\Facades\Pasax;
use Pasax\SecureMonitor\Models\PasaxAllowedIp;
use Pasax\SecureMonitor\Models\PasaxBlockedIp;
use Pasax\SecureMonitor\Models\PasaxIpUnblockRequest;
use Pasax\SecureMonitor\Models\PasaxLog;
use Pasax\SecureMonitor\Models\PasaxLoginAttempt;
use Pasax\SecureMonitor\Models\PasaxTrustedIp;
use Pasax\SecureMonitor\Models\PasaxUserLogin;
use Pasax\SecureMonitor\Services\PasaxServerSecurityService;

/**
 * REST API Keamanan Lengkap (Full Custom JSON API):
 * Menyediakan seluruh 8 modul keamanan Pasax dalam format JSON murni
 * untuk integrasi mobile apps, front-end modern (React/Next.js/Vue), atau CLI eksternal.
 */
class KeamananController extends Controller
{
    // =========================================================================
    // 1. OVERVIEW / RINGKASAN
    // =========================================================================

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Status dan statistik keamanan WAF.',
            'data' => [
                'waf' => [
                    'enabled' => Pasax::isEnabled(),
                    'block_enforcement' => Pasax::isBlockEnforcementEnabled(),
                ],
                'stats' => Pasax::stats(),
                'level_breakdown' => Pasax::levelBreakdown(24),
                'top_attackers' => Pasax::topAttackers(5),
            ],
        ]);
    }

    // =========================================================================
    // 2. LOG ANCAMAN WAF
    // =========================================================================

    public function logs(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

        $logs = PasaxLog::query()
            ->when($request->filled('level'), fn ($q) => $q->where('threat_level', $request->query('level')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->query('search');
                $query->where(function ($sub) use ($search) {
                    $sub->where('ip_address', 'like', "%{$search}%")
                        ->orWhere('path', 'like', "%{$search}%")
                        ->orWhere('rule_label', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function showLog(int $id): JsonResponse
    {
        $log = PasaxLog::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log,
        ]);
    }

    public function destroyLog(int $id): JsonResponse
    {
        PasaxLog::destroy($id);

        return response()->json([
            'success' => true,
            'message' => "Log ancaman #{$id} berhasil dihapus.",
        ]);
    }

    public function clearLogs(): JsonResponse
    {
        PasaxLog::truncate();

        return response()->json([
            'success' => true,
            'message' => 'Semua riwayat log ancaman WAF berhasil dibersihkan.',
        ]);
    }

    // =========================================================================
    // 3. IP DIBLOKIR & QUARANTINE
    // =========================================================================

    public function blockedIps(Request $request): JsonResponse
    {
        $activeOnly = $request->boolean('active_only', true);

        $query = PasaxBlockedIp::query();
        if ($activeOnly) {
            $query->active();
        }

        $items = $query->latest('id')->get();

        return response()->json([
            'success' => true,
            'count' => $items->count(),
            'data' => $items,
        ]);
    }

    public function checkIp(string $ip): JsonResponse
    {
        $isBlocked = Pasax::isBlocked($ip);
        $record = $isBlocked ? PasaxBlockedIp::query()->active()->where('ip_address', $ip)->first() : null;

        return response()->json([
            'success' => true,
            'data' => [
                'ip_address' => $ip,
                'is_blocked' => $isBlocked,
                'details' => $record,
            ],
        ]);
    }

    public function blockIp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ip' => ['required', 'ip'],
            'reason' => ['nullable', 'string', 'max:255'],
            'hours' => ['nullable', 'integer', 'min:1', 'max:8760'],
            'scope' => ['nullable', 'string', 'in:ip,device'],
        ]);

        $ip = $validated['ip'];
        $reason = $validated['reason'] ?? 'Diblokir manual via API Keamanan Kustom';
        $durationHours = ! empty($validated['hours']) ? (int) $validated['hours'] : null;
        $scope = $validated['scope'] ?? 'ip';

        Pasax::blockIp(
            ip: $ip,
            reason: $reason,
            durationHours: $durationHours,
            source: 'api',
            userId: $request->user()?->id ? (int) $request->user()->id : null,
            scope: $scope
        );

        $record = PasaxBlockedIp::query()->where('ip_address', $ip)->first();

        return response()->json([
            'success' => true,
            'message' => "IP {$ip} berhasil diblokir.",
            'data' => $record,
        ], 201);
    }

    public function unblockIp(string $ip): JsonResponse
    {
        $unblocked = Pasax::unblockIp($ip);

        return response()->json([
            'success' => true,
            'message' => $unblocked ? "Blokir untuk IP {$ip} berhasil dibuka." : 'IP tidak ditemukan.',
            'data' => [
                'ip_address' => $ip,
                'is_blocked' => Pasax::isBlocked($ip),
            ],
        ]);
    }

    public function toggleBlockedIp(int $id): JsonResponse
    {
        $item = PasaxBlockedIp::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);

        return response()->json([
            'success' => true,
            'message' => "Status IP {$item->ip_address} kini: ".($item->is_active ? 'Aktif' : 'Nonaktif'),
            'data' => $item,
        ]);
    }

    public function destroyBlockedIp(int $id): JsonResponse
    {
        PasaxBlockedIp::destroy($id);

        return response()->json([
            'success' => true,
            'message' => "Rekaman blokir #{$id} berhasil dihapus.",
        ]);
    }

    // =========================================================================
    // 4. WHITELIST IP (ALLOWED IPS)
    // =========================================================================

    public function allowedIps(): JsonResponse
    {
        $items = PasaxAllowedIp::query()->latest('id')->get();

        return response()->json([
            'success' => true,
            'count' => $items->count(),
            'data' => $items,
        ]);
    }

    public function storeAllowedIp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ip_address' => ['required', 'string', 'max:50'],
            'label' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $entry = PasaxAllowedIp::create([
            'ip_address' => $validated['ip_address'],
            'label' => $validated['label'] ?: 'Whitelist API',
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
            'allowed_by' => $request->user()?->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "IP {$entry->ip_address} berhasil dimasukkan ke whitelist.",
            'data' => $entry,
        ], 201);
    }

    public function toggleAllowedIp(int $id): JsonResponse
    {
        $item = PasaxAllowedIp::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);

        return response()->json([
            'success' => true,
            'message' => "Status whitelist {$item->ip_address} kini: ".($item->is_active ? 'Aktif' : 'Nonaktif'),
            'data' => $item,
        ]);
    }

    public function destroyAllowedIp(int $id): JsonResponse
    {
        PasaxAllowedIp::destroy($id);

        return response()->json([
            'success' => true,
            'message' => "Whitelist #{$id} berhasil dihapus.",
        ]);
    }

    // =========================================================================
    // 5. SERVER AUDIT & WEBSHELL SCANNER
    // =========================================================================

    public function serverReport(Request $request, PasaxServerSecurityService $service): JsonResponse
    {
        $force = $request->boolean('refresh', false);
        $report = $service->scan($force);
        $baseline = $service->baselineSummary();

        return response()->json([
            'success' => true,
            'data' => [
                'report' => $report,
                'baseline' => $baseline,
            ],
        ]);
    }

    public function createBaseline(PasaxServerSecurityService $service): JsonResponse
    {
        $result = $service->createBaseline();

        return response()->json([
            'success' => true,
            'message' => 'Baseline integritas SHA-256 berhasil dibuat.',
            'data' => [
                'total_files' => count($result['files'] ?? []),
                'created_at' => $result['created_at'] ?? now()->toIso8601String(),
                'files' => $result['files'] ?? [],
            ],
        ], 201);
    }

    public function deleteBaseline(PasaxServerSecurityService $service): JsonResponse
    {
        $service->deleteBaseline();

        return response()->json([
            'success' => true,
            'message' => 'Baseline integritas SHA-256 berhasil dihapus.',
        ]);
    }

    public function deleteSuspiciousFile(Request $request, PasaxServerSecurityService $service): JsonResponse
    {
        $request->validate(['path' => ['required', 'string']]);

        $result = $service->deleteSuspiciousFile($request->input('path'));

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['success'] ? 200 : 422);
    }

    public function releaseLockout(int $id): JsonResponse
    {
        PasaxLoginAttempt::where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => "Kunci lockout akun #{$id} berhasil dibuka.",
        ]);
    }

    // =========================================================================
    // 6. SESI LOGIN PENGGUNA & IP TERPERCAYA
    // =========================================================================

    public function sessions(): JsonResponse
    {
        $sessions = PasaxUserLogin::query()->latest('id')->limit(50)->get();
        $trustedIps = PasaxTrustedIp::query()->latest('id')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'sessions' => $sessions,
                'trusted_ips' => $trustedIps,
            ],
        ]);
    }

    public function terminateSession(int $id): JsonResponse
    {
        $session = PasaxUserLogin::findOrFail($id);
        $session->update(['logout_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => "Sesi pengguna #{$id} berhasil diputus.",
        ]);
    }

    public function storeTrustedIp(Request $request): JsonResponse
    {
        $ip = $request->ip() ?: '127.0.0.1';

        $trusted = PasaxTrustedIp::firstOrCreate([
            'user_id' => $request->user()?->id,
            'ip_address' => $ip,
        ], [
            'device_name' => substr((string) $request->userAgent(), 0, 100),
        ]);

        return response()->json([
            'success' => true,
            'message' => "IP {$ip} berhasil disimpan sebagai IP terpercaya.",
            'data' => $trusted,
        ], 201);
    }

    public function destroyTrustedIp(int $id): JsonResponse
    {
        PasaxTrustedIp::destroy($id);

        return response()->json([
            'success' => true,
            'message' => "IP Terpercaya #{$id} berhasil dihapus.",
        ]);
    }

    // =========================================================================
    // 7. TIKET PERMOHONAN BANDING UNBLOCK
    // =========================================================================

    public function tickets(Request $request): JsonResponse
    {
        $status = $request->query('status');

        $tickets = PasaxIpUnblockRequest::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('id')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $tickets,
        ]);
    }

    public function showTicket(int $id): JsonResponse
    {
        $ticket = PasaxIpUnblockRequest::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $ticket,
        ]);
    }

    public function respondTicket(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $ticket = PasaxIpUnblockRequest::findOrFail($id);
        $ticket->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'resolved_by' => $request->user()?->id,
            'resolved_at' => now(),
        ]);

        if ($validated['status'] === 'approved') {
            Pasax::unblockIp($ticket->ip_address, $ticket->device_id);
        }

        return response()->json([
            'success' => true,
            'message' => "Tiket #{$ticket->ticket_number} berhasil diperbarui menjadi {$validated['status']}.",
            'data' => $ticket,
        ]);
    }

    public function destroyTicket(int $id): JsonResponse
    {
        PasaxIpUnblockRequest::destroy($id);

        return response()->json([
            'success' => true,
            'message' => "Tiket permohonan #{$id} berhasil dihapus.",
        ]);
    }

    // =========================================================================
    // 8. PENGATURAN WAF DINAMIS
    // =========================================================================

    public function settings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Pasax::getSettings(),
        ]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'auto_block_scope' => ['nullable', 'string', 'in:device,ip,auto'],
            'auto_block_enabled' => ['nullable', 'boolean'],
            'auto_block_threshold' => ['nullable', 'integer', 'min:1', 'max:100'],
            'auto_block_duration' => ['nullable', 'integer', 'min:0', 'max:8760'],
            'instant_block_enabled' => ['nullable', 'boolean'],
            'block_enforcement' => ['nullable', 'boolean'],
        ]);

        $updated = Pasax::updateSettings($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan WAF berhasil diperbarui.',
            'data' => $updated,
        ]);
    }
}
