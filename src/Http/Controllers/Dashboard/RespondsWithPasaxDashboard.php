<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Pasax\SecureMonitor\Support\PasaxDashboardView;

/**
 * Render dashboard pages as plain Blade views.
 *
 * Pasax hanya mendukung dashboard Blade murni (100% Pure PHP, tanpa
 * Livewire/Inertia/Vue).
 */
trait RespondsWithPasaxDashboard
{
    /**
     * Execute an action on the controller.
     *
     * Menghapus parameter {pasaxSlug} dari URL berotasi agar controller
     * menerima parameter rute yang sebenarnya (mis. $id) tanpa tergeser.
     *
     * @param  string  $method
     * @param  array<string, mixed>  $parameters
     * @return mixed
     */
    public function callAction($method, $parameters)
    {
        unset($parameters['pasaxSlug']);

        return parent::callAction($method, $parameters);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function dashboardResponse(string $blade, array $data): View
    {
        if (! view()->exists($blade)) {
            abort(500, 'Dashboard views are not published. Run: php artisan pasax:install --with-dashboard');
        }

        $appLocale = (string) config('app.locale', 'en');
        if (! in_array($appLocale, ['en', 'id'], true)) {
            $appLocale = 'en';
        }

        $locale = (string) (request()->query('lang')
            ?? (request()->hasSession() ? session('pasax_locale') : null)
            ?? request()->cookie('pasax_locale')
            ?? $appLocale);

        if (! in_array($locale, ['en', 'id'], true)) {
            $locale = $appLocale;
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);
        if (class_exists(\Carbon\Carbon::class)) {
            \Carbon\Carbon::setLocale($locale);
        }

        if (request()->hasSession() && request()->query('lang')) {
            session(['pasax_locale' => $locale]);
        }

        $data['currentLocale'] = $locale;
        $data['pasaxTimezone'] = PasaxDashboardView::timezone();

        return view($blade, $data);
    }
}
