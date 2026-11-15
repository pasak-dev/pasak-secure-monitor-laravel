<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Pasax\SecureMonitor\Services\PasaxDashboardUrlService;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('pasax.dashboard.enabled', true);
    config()->set('pasax.dashboard.prefix', 'pasax');
    config()->set('pasax.dashboard.rotating', true);
    config()->set('pasax.dashboard.single_use', true);
    config()->set('pasax.dashboard.grace_seconds', 120);
    config()->set('pasax.dashboard.token_length', 16);
    config()->set(
        'pasax.dashboard.secret',
        'WT3J5pZLlQJkVPueACpFbxzBH0g1mSoB0Gcn7yKhKVp9WTvi',
    );
});

test(
    'pintu masuk stabil /pasax mengalihkan (302) ke URL acak milik sesi',
    function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/pasax');

        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        expect($location)->toMatch('#/pasax-[A-Za-z0-9]{12,32}/?$#');
    },
);

test(
    'alias stabil /pasax/logs dan subpage lainnya dialihkan (302) ke slug sesi',
    function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/pasax/logs');

        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        expect($location)->toMatch('#/pasax-[A-Za-z0-9]{12,32}/logs#');
    },
);

test(
    'slug yang valid menyajikan halaman HTML dashboard dengan status 200',
    function () {
        $user = User::factory()->create();

        $entry = $this->actingAs($user)->get('/pasax');
        $targetUrl = $entry->headers->get('Location');
        $path = parse_url($targetUrl, PHP_URL_PATH);

        $page = $this->actingAs($user)->get($path);
        $page->assertStatus(200);
    },
);

test(
    'token diputar setiap akses halaman HTML sehingga URL baru dihasilkan',
    function () {
        $user = User::factory()->create();

        $entry = $this->actingAs($user)->get('/pasax');
        $firstPath = parse_url($entry->headers->get('Location'), PHP_URL_PATH);

        // Akses pertama dengan slug pertama
        $page1 = $this->actingAs($user)->get($firstPath);
        $page1->assertStatus(200);

        // Ambil slug terbaru dari sesi setelah rotasi
        $newSlug = session(PasaxDashboardUrlService::SESSION_KEY);

        expect($newSlug)
            ->not->toBeNull()
            ->and('/'.$newSlug)
            ->not->toBe($firstPath);
    },
);

test(
    'url lama di dalam masa tenggang grace period dialihkan ke url baru bukan 404',
    function () {
        $user = User::factory()->create();

        $entry = $this->actingAs($user)->get('/pasax');
        $firstPath = parse_url(
            (string) $entry->headers->get('Location'),
            PHP_URL_PATH,
        );

        // Kunjungi halaman sekali agar token berotasi
        $this->actingAs($user)->get($firstPath)->assertStatus(200);

        // Buka kembali URL lama yang baru saja diputar (simulasi reload browser atau back button)
        $reloadResponse = $this->actingAs($user)->get($firstPath);
        $reloadResponse->assertStatus(302);

        $redirectLocation = $reloadResponse->headers->get('Location');
        expect($redirectLocation)
            ->toMatch('#/pasax-[A-Za-z0-9]{12,32}#')
            ->and(parse_url((string) $redirectLocation, PHP_URL_PATH))
            ->not->toBe($firstPath);
    },
);

test(
    'reload setelah masa tenggang lewat dialihkan ke slug aktif, bukan 404',
    function () {
        $user = User::factory()->create();

        $entry = $this->actingAs($user)->get('/pasax');
        $firstPath = parse_url(
            (string) $entry->headers->get('Location'),
            PHP_URL_PATH,
        );

        $this->actingAs($user)->get($firstPath)->assertStatus(200);

        // Simulasikan halaman dibiarkan terbuka lebih dari grace_seconds
        // (mis. 30 menit) lalu di-reload.
        session([PasaxDashboardUrlService::PREV_AT_KEY => time() - 1800]);

        $reload = $this->actingAs($user)->get($firstPath);
        $reload->assertStatus(302);

        $location = (string) $reload->headers->get('Location');

        expect($location)->toMatch('#/pasax-[A-Za-z0-9]{12,32}#');

        $this->actingAs($user)
            ->get(parse_url($location, PHP_URL_PATH))
            ->assertStatus(200);
    },
);

test(
    'slug asing pada navigasi html dialihkan ke slug aktif sesi',
    function () {
        $user = User::factory()->create();

        // Navigasi GET dengan token yang bukan milik sesi (mis. setelah
        // login ulang / sesi baru) harus pulih, bukan berakhir 404.
        $response = $this->actingAs($user)->get('/pasax-fake123456789012/logs');

        $response->assertStatus(302);

        $location = (string) $response->headers->get('Location');

        expect($location)->toMatch('#/pasax-[A-Za-z0-9]{12,32}/logs#');

        $this->actingAs($user)
            ->get(parse_url($location, PHP_URL_PATH))
            ->assertStatus(200);
    },
);

test(
    'slug asing pada feed tetap 404 agar dashboard tetap tersamar',
    function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/pasax-fake123456789012/threat-map/feed')
            ->assertStatus(404);
    },
);

test(
    'endpoint polling threat-map/feed tidak memutar token agar tidak balapan',
    function () {
        $user = User::factory()->create();

        $entry = $this->actingAs($user)->get('/pasax');
        $firstPath = parse_url(
            (string) $entry->headers->get('Location'),
            PHP_URL_PATH,
        );

        $this->actingAs($user)
            ->get($firstPath.'/threat-map')
            ->assertStatus(200);

        // Ambil slug aktif dari sesi untuk feed
        $slug = session(PasaxDashboardUrlService::SESSION_KEY);
        expect($slug)->toBeString();

        $feed1 = $this->actingAs($user)->getJson(
            '/'.$slug.'/threat-map/feed',
        );
        $feed1->assertStatus(200);
        expect(session(PasaxDashboardUrlService::SESSION_KEY))->toBe($slug);

        $feed2 = $this->actingAs($user)->getJson(
            '/'.$slug.'/threat-map/feed',
        );
        $feed2->assertStatus(200);
        expect(session(PasaxDashboardUrlService::SESSION_KEY))->toBe($slug);
    },
);

test('pengguna yang belum login dialihkan ke halaman login', function () {
    $response = $this->get('/pasax');

    $response->assertRedirect(route('pasax.dashboard.login'));
});
