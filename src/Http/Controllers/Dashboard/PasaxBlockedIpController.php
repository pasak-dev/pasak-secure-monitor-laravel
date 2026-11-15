<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Pasax\SecureMonitor\Models\PasaxBlockedIp;
use Pasax\SecureMonitor\Models\PasaxIpUnblockRequest;
use Pasax\SecureMonitor\Models\PasaxLog;
use Pasax\SecureMonitor\Services\PasaxSentinelBridge;
use Pasax\SecureMonitor\Services\PasaxService;
use Pasax\SecureMonitor\Services\PasaxUserLoginService;
use Throwable;

/**
 * Blocked IP management page for the Blade monitoring dashboard.
 */
class PasaxBlockedIpController extends Controller
{
    use RespondsWithPasaxDashboard;

    public function index(Request $request): View
    {
        $query = PasaxBlockedIp::query()->latest('id');

        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', '');
        $dateFrom = (string) $request->query('date_from', '');
        $dateTo = (string) $request->query('date_to', '');
        $perPage = (int) $request->query('per_page', 25);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('device_id', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'expired') {
            $query->expired();
        } elseif ($status === 'permanent') {
            $query->whereNull('expires_at')->where('is_active', true);
        }

        if ($dateFrom !== '') {
            try {
                $query->where(
                    'created_at',
                    '>=',
                    Carbon::parse($dateFrom)->startOfDay(),
                );
            } catch (Throwable) {
            }
        }

        if ($dateTo !== '') {
            try {
                $query->where(
                    'created_at',
                    '<=',
                    Carbon::parse($dateTo)->endOfDay(),
                );
            } catch (Throwable) {
            }
        }

        return $this->dashboardResponse('pages.pasax.blocked-ips', [
            'blocks' => $query->paginate($perPage)->withQueryString(),
            'stats' => [
                'total' => PasaxBlockedIp::query()->count(),
                'active' => PasaxBlockedIp::query()->active()->count(),
                'permanent' => PasaxBlockedIp::query()
                    ->whereNull('expires_at')
                    ->where('is_active', true)
                    ->count(),
                'hits' => (int) PasaxBlockedIp::query()->sum('hit_count'),
            ],
            'filters' => [
                'search' => $search,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
            ],
            'urls' => [
                'index' => route('pasax.dashboard.blocked-ips'),
                'show' => route('pasax.dashboard.blocked-ips.show', [
                    'id' => '__ID__',
                ]),
                'store' => route('pasax.dashboard.blocked-ips.store'),
                'toggle' => route('pasax.dashboard.blocked-ips.toggle', [
                    'id' => '__ID__',
                ]),
                'destroy' => route('pasax.dashboard.blocked-ips.destroy', [
                    'id' => '__ID__',
                ]),
            ],
        ]);
    }

    public function show(int|string $id): View
    {
        $block = PasaxBlockedIp::query()->find($id);

        if (! $block) {
            abort(404);
        }

        $blockedAt = $block->blocked_at ?? $block->created_at;

        $leadingQuery = PasaxLog::query()
            ->where('ip_address', $block->ip_address);

        if ($blockedAt) {
            $leadingQuery->where('created_at', '<=', $blockedAt);
        }

        $logsLeadingToBlock = (clone $leadingQuery)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->limit(150)
            ->get();

        $logsAfterBlock = collect();
        if ($blockedAt) {
            $logsAfterBlock = PasaxLog::query()
                ->where('ip_address', $block->ip_address)
                ->where('created_at', '>', $blockedAt)
                ->latest('id')
                ->limit(50)
                ->get();
        }

        $totalLogsCount = PasaxLog::query()
            ->where('ip_address', $block->ip_address)
            ->count();

        $location = null;
        try {
            $location = app(PasaxUserLoginService::class)->resolveLocation($block->ip_address);
        } catch (Throwable) {
        }

        return $this->dashboardResponse('pages.pasax.blocked-ip-detail', [
            'block' => $block,
            'location' => $location,
            'relatedLogs' => $logsLeadingToBlock,
            'logsLeadingToBlock' => $logsLeadingToBlock,
            'logsAfterBlock' => $logsAfterBlock,
            'blockedAt' => $blockedAt,
            'totalLogsCount' => $totalLogsCount,
            'relatedTickets' => PasaxIpUnblockRequest::query()
                ->where('ip_address', $block->ip_address)
                ->latest('id')
                ->limit(10)
                ->get(),
            'urls' => [
                'index' => route('pasax.dashboard.blocked-ips'),
                'show' => route('pasax.dashboard.blocked-ips.show', [
                    'id' => '__ID__',
                ]),
                'logShow' => route('pasax.dashboard.logs.show', [
                    'id' => '__ID__',
                ]),
                'ticketShow' => route('pasax.dashboard.tickets.show', [
                    'id' => '__ID__',
                ]),
                'toggle' => route('pasax.dashboard.blocked-ips.toggle', [
                    'id' => $block->getKey(),
                ]),
                'destroy' => route('pasax.dashboard.blocked-ips.destroy', [
                    'id' => $block->getKey(),
                ]),
            ],
        ]);
    }

    public function store(
        Request $request,
        PasaxService $security,
    ): RedirectResponse {
        $validated = $request->validate([
            'ip_address' => ['required', 'string', 'max:45'],
            'block_scope' => ['nullable', 'string', 'in:ip,device'],
            'device_id' => ['nullable', 'string', 'max:191'],
            'local_ip' => ['nullable', 'string', 'max:45'],
            'reason' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'duration_hours' => ['nullable', 'integer', 'min:0'],
            'is_permanent' => ['nullable', 'boolean'],
        ]);

        $ip = trim($validated['ip_address']);

        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            throw ValidationException::withMessages([
                'ip_address' => __('Invalid IP address.'),
            ]);
        }

        if ($ip === $security->resolveClientIp($request)) {
            throw ValidationException::withMessages([
                'ip_address' => __('You cannot block your own IP address.'),
            ]);
        }

        if ($security->isWhitelisted($ip)) {
            throw ValidationException::withMessages([
                'ip_address' => __(
                    'This IP is whitelisted and cannot be blocked.',
                ),
            ]);
        }

        $duration = isset($validated['duration_hours'])
            ? (int) $validated['duration_hours']
            : 24;
        $isPermanent = (bool) ($validated['is_permanent'] ?? $duration === 0);
        $scope = $validated['block_scope'] ?? 'ip';
        $deviceId = $validated['device_id'] ?? null;
        $localIp = $validated['local_ip'] ?? null;

        $security->block($ip, [
            'block_scope' => $scope,
            'device_id' => $deviceId,
            'local_ip' => $localIp,
            'reason' => $validated['reason'] ?? __('Blocked manually by administrator'),
            'notes' => $validated['notes'] ?? null,
            'source' => 'manual',
            'blocked_by' => $request->user()?->getKey(),
            'duration_hours' => $isPermanent ? 0 : $duration,
            'expires_at' => $isPermanent ? null : now()->addHours($duration),
        ]);

        if ($scope === 'ip') {
            try {
                app(PasaxSentinelBridge::class)->blockIp(
                    $ip,
                    $validated['reason'] ??
                        __('Blocked manually by administrator'),
                    $isPermanent ? 0 : $duration * 60,
                );
            } catch (Throwable) {
            }
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => __('IP :ip has been blocked.', ['ip' => $ip]),
        ]);
    }

    public function toggle(int|string $id): RedirectResponse
    {
        $block = PasaxBlockedIp::query()->find($id);

        if (! $block) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => __('Blocked IP not found.'),
            ]);
        }

        $block->is_active = ! $block->is_active;
        $block->save();

        return back()->with('toast', [
            'type' => 'success',
            'message' => $block->is_active
                ? __('Block for :ip re-enabled.', ['ip' => $block->ip_address])
                : __('Block for :ip disabled.', ['ip' => $block->ip_address]),
        ]);
    }

    public function destroy(int|string $id): RedirectResponse
    {
        $block = PasaxBlockedIp::query()->find($id);

        if (! $block) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => __('Blocked IP not found.'),
            ]);
        }

        $ip = $block->ip_address;
        $block->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => __('IP :ip has been unblocked.', ['ip' => $ip]),
        ]);
    }
}
