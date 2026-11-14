<?php

namespace Pasak\SecureMonitor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pasak\SecureMonitor\Services\PasakUserLoginService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pencegat akses dashboard Pasak dengan form login mandiri.
 *
 * Memeriksa apakah pengguna sudah login via sesi dashboard Pasak
 * (`pasak_dashboard_authenticated === true`). Jika belum, request akan dicegat
 * dan dialihkan ke form login (/pasak/login).
 */
class PasakDashboardAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika autentikasi dashboard dinonaktifkan secara eksplisit
        if (! (bool) config('pasak.dashboard.auth.enabled', true)) {
            $this->trackDashboardAccess($request);

            return $next($request);
        }

        // Jika sesi sudah terotentikasi via login Pasak
        if ($request->hasSession() && $request->session()->get('pasak_dashboard_authenticated') === true) {
            $this->trackDashboardAccess($request);

            return $next($request);
        }

        // Dukungan untuk unit testing Testbench yang memakai actingAs($admin)
        if (app()->runningUnitTests() && $request->user() !== null && ! $request->attributes->get('force_pasak_auth', false)) {
            $this->trackDashboardAccess($request);

            return $next($request);
        }

        // Jika request via AJAX / JSON API
        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'message' => 'Unauthenticated from Pasak dashboard.',
                'login_url' => route('pasak.dashboard.login'),
            ], 401);
        }

        // Simpan URL yang dituju agar setelah login dapat dialihkan kembali
        if ($request->hasSession() && $request->isMethod('GET')) {
            $request->session()->put('pasak_dashboard_intended', $request->fullUrl());
        }

        return redirect()->guest(route('pasak.dashboard.login'));
    }

    protected function trackDashboardAccess(Request $request): void
    {
        try {
            app(PasakUserLoginService::class)->trackDashboardAccess($request, $request->user());
        } catch (\Throwable) {
        }
    }
}
