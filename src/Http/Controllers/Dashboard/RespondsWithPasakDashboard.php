<?php

namespace Internal\Pasak\Http\Controllers\Dashboard;

use Illuminate\Contracts\View\View;

/**
 * Render dashboard pages as plain Blade views.
 *
 * Pasak hanya mendukung dashboard Blade murni (100% Pure PHP, tanpa
 * Livewire/Inertia/Vue).
 */
trait RespondsWithPasakDashboard
{
    /**
     * Execute an action on the controller.
     *
     * Menghapus parameter {pasakSlug} dari URL berotasi agar controller
     * menerima parameter rute yang sebenarnya (mis. $id) tanpa tergeser.
     *
     * @param  string  $method
     * @param  array<string, mixed>  $parameters
     * @return mixed
     */
    public function callAction($method, $parameters)
    {
        unset($parameters['pasakSlug']);

        return parent::callAction($method, $parameters);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function dashboardResponse(string $blade, array $data): View
    {
        if (! view()->exists($blade)) {
            abort(500, 'Dashboard views are not published. Run: php artisan pasak:install --with-dashboard');
        }

        $locale = (string) (request()->query('lang')
            ?? (request()->hasSession() ? session('pasak_locale') : null)
            ?? request()->cookie('pasak_locale')
            ?? 'en');

        if (! in_array($locale, ['en', 'id'], true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

        if (request()->hasSession()) {
            session(['pasak_locale' => $locale]);
        }

        $data['currentLocale'] = $locale;

        return view($blade, $data);
    }
}
