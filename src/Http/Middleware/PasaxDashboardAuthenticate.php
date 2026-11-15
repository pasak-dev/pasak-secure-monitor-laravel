<?php

namespace Pasax\SecureMonitor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pasax\SecureMonitor\Services\PasaxUserLoginService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pencegat akses dashboard Pasax dengan form login mandiri.
 *
 * Memeriksa apakah pengguna sudah login via sesi dashboard Pasax
 * (`pasax_dashboard_authenticated === true`). Jika belum, request akan dicegat
 * dan dialihkan ke form login (/pasax/login).
 */
class PasaxDashboardAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika autentikasi dashboard dinonaktifkan secara eksplisit
        if (! (bool) config('pasax.dashboard.auth.enabled', true)) {
            $this->trackDashboardAccess($request);

            return $next($request);
        }

        // Jika sesi sudah terotentikasi via login Pasax
        if ($request->hasSession() && $request->session()->get('pasax_dashboard_authenticated') === true) {
            $this->trackDashboardAccess($request);

            return $next($request);
        }

        // Dukungan untuk unit testing Testbench yang memakai actingAs($admin)
        if (app()->runningUnitTests() && $request->user() !== null && ! $request->attributes->get('force_pasax_auth', false)) {
            $this->trackDashboardAccess($request);

            return $next($request);
        }

        // Jika request via AJAX / JSON API
        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'message' => 'Unauthenticated from Pasax dashboard.',
                'login_url' => route('pasax.dashboard.login'),
            ], 401);
        }

        // Simpan URL yang dituju agar setelah login dapat dialihkan kembali
        if ($request->hasSession() && $request->isMethod('GET')) {
            $request->session()->put('pasax_dashboard_intended', $request->fullUrl());
        }

        return redirect()->guest(route('pasax.dashboard.login'));
    }

    protected function trackDashboardAccess(Request $request): void
    {
        try {
            app(PasaxUserLoginService::class)->trackDashboardAccess($request, $request->user());
        } catch (\Throwable) {
        }
    }
}
