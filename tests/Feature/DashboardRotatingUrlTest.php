<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxBlockedIpController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxDashboardEntryController;
use Pasax\SecureMonitor\Http\Middleware\PasaxRotatingDashboard;
use Pasax\SecureMonitor\Models\PasaxBlockedIp;
use Pasax\SecureMonitor\Services\PasaxDashboardUrlService;

beforeEach(function () {
    config()->set('pasax.dashboard.enabled', true);
    config()->set('pasax.dashboard.prefix', 'pasax');
    config()->set('pasax.dashboard.rotating', true);
    config()->set('pasax.dashboard.single_use', true);
    config()->set('pasax.dashboard.grace_seconds', 120);
    config()->set('pasax.dashboard.token_length', 16);

    $admin = $this->createAdminUser();
    $this->actingAs($admin);

    Route::middleware(['web', 'auth'])
        ->get('pasax', PasaxDashboardEntryController::class)
        ->name('pasax.dashboard.entry');

    Route::middleware(['web', 'auth', PasaxRotatingDashboard::class])
        ->prefix('{pasaxSlug}')
        ->where(['pasaxSlug' => 'pasax-[A-Za-z0-9]{12,32}'])
        ->name('pasax.dashboard.')
        ->group(function () {
            Route::get(
                '/',
                fn () => response('overview '.route('pasax.dashboard.logs')),
            )->name('overview');
            Route::get(
                'logs',
                fn () => response('logs '.route('pasax.dashboard.logs')),
            )->name('logs');
            Route::get(
                'threat-map',
                fn () => response(
                    'map '.route('pasax.dashboard.threat-map.feed'),
                ),
            )->name('threat-map');
            Route::get(
                'threat-map/feed',
                fn () => response()->json(['ok' => true]),
            )->name('threat-map.feed');
            Route::post(
                'action',
                fn () => response('ok'),
            )->name('action');
            Route::get(
                'blocked-ips',
                fn () => response('blocked-ips: '.json_encode(session('toast'))),
            )->name('blocked-ips');
            Route::patch(
                'blocked-ips/{id}/toggle',
                [PasaxBlockedIpController::class, 'toggle'],
            )->name('blocked-ips.toggle');
        });

    Route::getRoutes()->refreshNameLookups();
});

test('service membuat slug acak berformat pasax-token', function () {
    $service = app(PasaxDashboardUrlService::class);
    $request = Request::create('/pasax', 'GET');

    $slug = $service->currentSlug($request);

    expect($slug)
        ->toStartWith('pasax-')
        ->and($service->matchesFormat($slug))
        ->toBeTrue()
        ->and(strlen($slug) - strlen('pasax-'))
        ->toBe(16);
});

test('pintu stabil mengalihkan ke slug acak milik sesi', function () {
    $response = $this->get('/pasax');

    $response->assertRedirect();
    $target = $response->headers->get('Location');

    expect($target)->toMatch('/pasax-[A-Za-z0-9]{12,32}/');
});

test(
    'slug valid tersaji dan link memakai token baru sekali pakai',
    function () {
        $entry = $this->get('/pasax');
        $firstUrl = $entry->headers->get('Location');
        $firstPath = parse_url($firstUrl, PHP_URL_PATH);

        $page = $this->get($firstPath.'/logs');
        $page->assertOk();

        $body = $page->getContent();
        preg_match('/pasax-[A-Za-z0-9]{12,32}/', (string) $body, $m);

        expect($m[0] ?? '')
            ->not->toBe('')
            ->and($m[0] ?? '')
            ->not->toBe(trim($firstPath, '/'));
    },
);

test('reload url lama dialihkan ke url baru bukan 404', function () {
    $entry = $this->get('/pasax');
    $firstPath = parse_url(
        (string) $entry->headers->get('Location'),
        PHP_URL_PATH,
    );

    $first = $this->get($firstPath);
    $first->assertOk();

    // Reload URL yang sama (sudah diputar): harus 302 ke slug baru.
    $reload = $this->get($firstPath);
    $reload->assertRedirect();

    $newPath = parse_url(
        (string) $reload->headers->get('Location'),
        PHP_URL_PATH,
    );

    expect($newPath)
        ->not->toBe($firstPath)
        ->and((string) $newPath)
        ->toMatch('/pasax-[A-Za-z0-9]{12,32}/');

    // Mengikuti redirect hasilnya 200.
    $this->get($newPath)->assertOk();
});

test(
    'reload setelah masa tenggang lewat dialihkan ke slug aktif, bukan 404',
    function () {
        $entry = $this->get('/pasax');
        $firstPath = parse_url(
            (string) $entry->headers->get('Location'),
            PHP_URL_PATH,
        );

        $this->get($firstPath)->assertOk();

        // Simulasikan halaman dibiarkan terbuka lebih dari grace_seconds.
        session([PasaxDashboardUrlService::PREV_AT_KEY => time() - 600]);

        $reload = $this->get($firstPath);
        $reload->assertRedirect();

        $newPath = parse_url(
            (string) $reload->headers->get('Location'),
            PHP_URL_PATH,
        );

        expect((string) $newPath)
            ->toMatch('/pasax-[A-Za-z0-9]{12,32}/')
            ->not->toBe(trim((string) $firstPath, '/'));

        $this->get($newPath)->assertOk();
    },
);

test(
    'slug asing pada navigasi html dialihkan ke slug aktif sesi',
    function () {
        $response = $this->get('/pasax-zzzzzzzzzzzzzzzz/logs');

        $response->assertRedirect();

        $target = (string) $response->headers->get('Location');

        expect($target)->toMatch('/pasax-[A-Za-z0-9]{12,32}\\/logs/');

        $this->get(parse_url($target, PHP_URL_PATH))->assertOk();
    },
);

test('slug asing pada feed/tulisan tetap 404 agar tersamar', function () {
    $this->get('/pasax-zzzzzzzzzzzzzzzz/threat-map/feed')->assertNotFound();
    $this->post('/pasax-zzzzzzzzzzzzzzzz/action')->assertNotFound();
    $this->get('/pasax/logs')->assertNotFound();
});

test(
    'mengganti secret menandatangani ulang dan URL lama tetap pulih',
    function () {
        config()->set('pasax.dashboard.secret', 'secret-lama-1234567890');

        $entry = $this->get('/pasax');
        $oldPath = parse_url(
            (string) $entry->headers->get('Location'),
            PHP_URL_PATH,
        );

        $this->get($oldPath)->assertOk();

        config()->set('pasax.dashboard.secret', 'secret-baru-0987654321');

        $response = $this->get($oldPath);
        $response->assertRedirect();

        $newPath = parse_url(
            (string) $response->headers->get('Location'),
            PHP_URL_PATH,
        );

        expect($newPath)
            ->toMatch('/pasax-[A-Za-z0-9]{12,32}/')
            ->not->toBe(trim((string) $oldPath, '/'));

        $this->get($newPath)->assertOk();
    },
);

test('feed polling tidak memutar token sehingga tidak balapan', function () {
    $entry = $this->get('/pasax');
    $firstPath = parse_url(
        (string) $entry->headers->get('Location'),
        PHP_URL_PATH,
    );

    $this->get($firstPath.'/threat-map')->assertOk();

    // Ambil slug aktif dari sesi untuk feed.
    $service = app(PasaxDashboardUrlService::class);
    $current = session(PasaxDashboardUrlService::SESSION_KEY);

    expect($current)->toBeString();

    $this->get('/'.$current.'/threat-map/feed')->assertOk();
    $this->get('/'.$current.'/threat-map/feed')->assertOk();
});

test('rute berparameter seperti toggle blocked-ip menerima id sebenarnya bukan pasaxSlug', function () {
    $block = PasaxBlockedIp::create([
        'ip_address' => '198.51.100.77',
        'is_active' => true,
    ]);

    $this->get('/pasax');
    $current = session(PasaxDashboardUrlService::SESSION_KEY);

    $toggleUrl = '/'.$current.'/blocked-ips/'.$block->id.'/toggle';

    $response = $this->patch($toggleUrl);
    $response->assertSessionHas('toast.type', 'success');

    $block->refresh();
    expect($block->is_active)->toBeFalse();

    // Toggle kembali menjadi true
    $response2 = $this->patch($toggleUrl);
    $response2->assertSessionHas('toast.type', 'success');

    $block->refresh();
    expect($block->is_active)->toBeTrue();
});

test('flash toast bertahan melewati pengalihan ganda url acak berputar', function () {
    $block = PasaxBlockedIp::create([
        'ip_address' => '198.51.100.88',
        'is_active' => true,
    ]);

    $this->get('/pasax');
    $current = session(PasaxDashboardUrlService::SESSION_KEY);

    $this->get('/'.$current.'/blocked-ips');
    $newer = session(PasaxDashboardUrlService::SESSION_KEY);

    $response = $this->patch('/'.$current.'/blocked-ips/'.$block->id.'/toggle');
    $response->assertRedirect('/'.$current.'/blocked-ips');

    $landing = $this->followRedirects($response);
    $landing->assertOk();
    expect($landing->getContent())->toContain('disabled');
});
