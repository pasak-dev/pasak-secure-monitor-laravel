<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;

function publishBladeSupportModal(): string
{
    $target = resource_path('views/pages/pasak');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak',
            $target
        );
    } else {
        File::copy(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak/page-support-modal.blade.php',
            $target.'/page-support-modal.blade.php'
        );
        File::copy(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak/blocked-ips.blade.php',
            $target.'/blocked-ips.blade.php'
        );
    }

    return $target;
}

function registerSupportModalDummyRoutes(): void
{
    foreach ([
        'overview', 'logs', 'logs.show', 'logs.clear', 'logs.destroy',
        'blocked-ips', 'blocked-ips.show', 'blocked-ips.store', 'blocked-ips.toggle', 'blocked-ips.destroy',
        'allowed-ips', 'allowed-ips.store', 'allowed-ips.toggle', 'allowed-ips.destroy',
        'server', 'server.baseline.create', 'server.baseline.destroy', 'server.optimize.clear', 'server.suspicious.verify', 'server.suspicious.delete',
        'sessions', 'sessions.destroy', 'trusted-ips.store-my-ip', 'trusted-ips.destroy',
        'tickets', 'tickets.show', 'tickets.respond', 'tickets.destroy',
        'settings', 'settings.update',
        'threat-map', 'threat-map.feed',
    ] as $name) {
        Route::any('/__pasak_support_'.$name, fn () => 'ok')->name('pasak.dashboard.'.$name);
    }

    Route::getRoutes()->refreshNameLookups();
}

beforeEach(function () {
    app()->setLocale('id');
    config()->set('pasak.dashboard.driver', 'blade');
    publishBladeSupportModal();
    registerSupportModalDummyRoutes();
});

test('every registered page guide in support modal has complete bilingual sections', function () {
    $html = Blade::render(
        '@extends("pages.pasak.layout") @section("page_support_key", "overview") @section("content") Test @endsection',
        ['errors' => new ViewErrorBag]
    );

    expect($html)->toContain('pasakPageSupportModal');
    expect($html)->toContain('pasakSupportModalTitle');
    expect($html)->toContain('pasakSwitchGuideLang');
});

test('support modal on blocked-ips page displays comprehensive purpose, metrics, and actions', function () {
    $view = view('pages.pasak.blocked-ips', [
        'stats' => ['total' => 25, 'active' => 7, 'permanent' => 3, 'hits' => 142],
        'filters' => [],
        'blocks' => new LengthAwarePaginator([], 0, 25),
        'errors' => new ViewErrorBag,
    ])->render();

    // Verify Title & Badges
    expect(str_contains($view, 'Karantina IP &amp; Perangkat Terblokir') || str_contains($view, 'Karantina IP & Perangkat Terblokir'))->toBeTrue();
    expect(str_contains($view, 'Quarantined &amp; Blocked IPs') || str_contains($view, 'Quarantined & Blocked IPs'))->toBeTrue();
    expect(str_contains($view, 'Karantina Aktif WAF &amp; Kernel Linux') || str_contains($view, 'Karantina Aktif WAF & Kernel Linux'))->toBeTrue();

    // Verify Detailed Purpose & Strategy
    expect($view)->toContain('HTTP 403 Forbidden');
    expect($view)->toContain('Pusat penegakan dan buku besar karantina');

    // Verify Information & Metrics Displayed
    expect($view)->toContain('Kartu Total Pemblokiran');
    expect($view)->toContain('Kartu Karantina Aktif');
    expect($view)->toContain('Kartu Blokir Permanen');
    expect($view)->toContain('Kartu Total Hits Digagalkan');
    expect($view)->toContain('Lingkup Sanksi (IP vs Perangkat)');
    expect($view)->toContain('WiFi-Safe');

    // Verify Available Actions
    expect($view)->toContain('Tambah Blokir Manual');
    expect(str_contains($view, 'Pencarian &amp; Filter Fleksibel') || str_contains($view, 'Pencarian & Filter Fleksibel'))->toBeTrue();
    expect($view)->toContain('Ubah Status Aktif / Nonaktif');
    expect($view)->toContain('Hapus Catatan Permanen');
    expect($view)->toContain('Buka Intelijen Forensik');

    // Verify Pro-Tips
    expect($view)->toContain('false-positive');
});

test('support modal dynamically detects page from page_support_key section or url path', function () {
    // 1. Overview
    $htmlOverview = Blade::render(
        '@extends("pages.pasak.layout") @section("page_support_key", "overview") @section("content") Test @endsection',
        ['errors' => new ViewErrorBag]
    );
    expect($htmlOverview)->toContain('Ringkasan Keamanan (Security Overview)');

    // 2. Server Audit
    $htmlServer = Blade::render(
        '@extends("pages.pasak.layout") @section("page_support_key", "server") @section("content") Test @endsection',
        ['errors' => new ViewErrorBag]
    );
    expect(str_contains($htmlServer, 'Keamanan Server &amp; Integritas Berkas') || str_contains($htmlServer, 'Keamanan Server & Integritas Berkas'))->toBeTrue();
    expect($htmlServer)->toContain('Buat Ulang Baseline Integritas');

    // 3. User Sessions
    $htmlSessions = Blade::render(
        '@extends("pages.pasak.layout") @section("page_support_key", "sessions") @section("content") Test @endsection',
        ['errors' => new ViewErrorBag]
    );
    expect(str_contains($htmlSessions, 'Sesi Pengguna &amp; Riwayat Login') || str_contains($htmlSessions, 'Sesi Pengguna & Riwayat Login'))->toBeTrue();
    expect($htmlSessions)->toContain('Simpan IP Saat Ini sebagai Terpercaya');

    // 4. Allowed IPs
    $htmlAllowed = Blade::render(
        '@extends("pages.pasak.layout") @section("page_support_key", "allowed-ips") @section("content") Test @endsection',
        ['errors' => new ViewErrorBag]
    );
    expect($htmlAllowed)->toContain('Daftar IP Diizinkan (Allowlist / Whitelist)');

    // 5. Threat Map
    $htmlThreatMap = Blade::render(
        '@extends("pages.pasak.layout") @section("page_support_key", "threat-map") @section("content") Test @endsection',
        ['errors' => new ViewErrorBag]
    );
    expect($htmlThreatMap)->toContain('Peta Ancaman Siber Real-Time');

    // 6. Settings
    $htmlSettings = Blade::render(
        '@extends("pages.pasak.layout") @section("page_support_key", "settings") @section("content") Test @endsection',
        ['errors' => new ViewErrorBag]
    );
    expect(str_contains($htmlSettings, 'Pengaturan WAF &amp; Sensor Keamanan') || str_contains($htmlSettings, 'Pengaturan WAF & Sensor Keamanan'))->toBeTrue();
});
