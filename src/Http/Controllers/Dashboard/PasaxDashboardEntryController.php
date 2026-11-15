<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Pasax\SecureMonitor\Services\PasaxDashboardUrlService;

/**
 * Pintu masuk stabil "/pasax" untuk mode URL berputar.
 *
 * Pengguna menandai /pasax, setiap kunjungan dialihkan (302) ke slug acak
 * aktif "/pasax-{token}/" milik sesinya. Slug langsung (dengan token) yang
 * dipakai untuk navigasi harian sehingga scanner tidak bisa menebak URL.
 */
class PasaxDashboardEntryController extends Controller
{
    public function __invoke(Request $request, PasaxDashboardUrlService $urls): RedirectResponse
    {
        $slug = $urls->currentSlug($request);
        $urls->applyUrlDefaults($slug);

        if ($request->hasSession()) {
            $request->session()->reflash();
        }

        $query = $request->getQueryString();

        return redirect()->to(url($slug).($query !== null && $query !== '' ? '?'.$query : ''), 302);
    }
}
