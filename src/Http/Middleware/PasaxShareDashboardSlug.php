<?php

namespace Pasax\SecureMonitor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pasax\SecureMonitor\Services\PasaxDashboardUrlService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menjamin route('pasax.dashboard.*') tidak pernah fatal di luar dashboard.
 *
 * View milik host (mis. navigasi di layout) boleh memanggil route dashboard
 * dari halaman mana pun. Middleware ringan ini (grup "web") mengisi
 * URL::defaults dari slug sesi bila ada, atau membiarkan fallback bawaan
 * (prefix stabil "/pasax/...") yang ditangani alias redirect. Tanpa ini,
 * parameter {pasaxSlug} yang wajib membuat seluruh halaman host 500.
 */
class PasaxShareDashboardSlug
{
    public function __construct(
        protected PasaxDashboardUrlService $urls,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->urls->isRotatingEnabled()) {
            try {
                if ($request->hasSession()) {
                    $stored = $request->session()->get(PasaxDashboardUrlService::SESSION_KEY);

                    if (is_string($stored) && $this->urls->matchesFormat($stored)) {
                        $this->urls->applyUrlDefaults($stored);
                    }
                }
            } catch (\Throwable) {
                // Abaikan — fallback bawaan tetap berlaku.
            }
        }

        return $next($request);
    }
}
