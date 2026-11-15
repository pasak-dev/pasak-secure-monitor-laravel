<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Pasax\SecureMonitor\Services\PasaxDashboardUrlService;

/**
 * Alias stabil "/pasax/*" untuk mode URL berputar.
 *
 * Bookmark lama, link layout host, dan route() di luar konteks dashboard
 * menghasilkan URL stabil ini; semuanya dialihkan (302) ke slug acak aktif
 * milik sesi pengguna dengan path + query dipertahankan:
 *
 *   /pasax/logs  → 302 → /pasax-a1b2c3d4e5f6/logs
 */
class PasaxDashboardAliasController extends Controller
{
    public function __invoke(Request $request, PasaxDashboardUrlService $urls): RedirectResponse
    {
        $slug = $urls->currentSlug($request);
        $urls->applyUrlDefaults($slug);

        if ($request->hasSession()) {
            $request->session()->reflash();
        }

        $any = trim((string) ($request->route('any') ?? ''), '/');
        $url = url($slug.($any === '' ? '' : '/'.$any));
        $query = $request->getQueryString();

        return redirect()->to($url.(is_string($query) && $query !== '' ? '?'.$query : ''), 302);
    }
}
