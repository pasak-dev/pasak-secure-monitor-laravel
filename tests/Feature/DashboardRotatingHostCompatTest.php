<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

function publishPasaxViewsForCompat(): string
{
    $target = resource_path('views/pages/pasax');

    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasax',
            $target,
        );
    }

    return $target;
}

function loadRealRotatingRoutes(): void
{
    config()->set('pasax.dashboard.enabled', true);
    config()->set('pasax.dashboard.rotating', true);
    config()->set('pasax.dashboard.prefix', 'pasax');
    config()->set('pasax.dashboard.single_use', true);
    config()->set('pasax.dashboard.grace_seconds', 120);
    config()->set('pasax.dashboard.token_length', 16);

    // Simulasi fallback boot PasaxServiceProvider::registerDashboardUrlDefaults().
    URL::defaults(['pasaxSlug' => 'pasax']);

    if (! Route::has('pasax.dashboard.entry')) {
        require dirname(__DIR__, 2).'/routes/pasax-web-blade.php';

        // Testbench tidak memanggil RouteServiceProvider (yang me-refresh
        // name lookup), jadi segarkan manual seperti aplikasi real.
        Route::getRoutes()->refreshNameLookups();
    }
}

beforeEach(function () {
    publishPasaxViewsForCompat();
    loadRealRotatingRoutes();

    $this->actingAs($this->createAdminUser());
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/pages/pasax'));
});

test(
    'host view calling dashboard route helper never fatals in rotating mode',
    function () {
        Route::middleware(['web'])->get(
            '/__pasax_host',
            fn () => 'link: '.route('pasax.dashboard.logs'),
        );

        $response = $this->get('/__pasax_host');

        $response->assertOk();

        // Fallback stabil — alias redirect yang akan mengantar ke token aktif.
        expect($response->getContent())->toContain('/pasax/logs');
    },
);

test('stable alias redirects to the active rotating slug', function () {
    $response = $this->get('/pasax/logs');

    $response->assertRedirect();

    $target = (string) $response->headers->get('Location');

    expect($target)->toMatch('/pasax-[A-Za-z0-9]{12,32}\\/logs/');

    // Ikuti redirect: halaman log tersaji dengan link token baru.
    $page = $this->get(parse_url($target, PHP_URL_PATH));
    $page->assertOk();
});

test(
    'entry plus alias keep old bookmarks working across rotation',
    function () {
        $entry = $this->get('/pasax');
        $entry->assertRedirect();

        $first = (string) $entry->headers->get('Location');
        $firstPath = parse_url($first, PHP_URL_PATH);

        // Kunjungi via slug, lalu buka lagi bookmark stabil lama.
        $this->get($firstPath)->assertOk();

        $alias = $this->get('/pasax/threat-map');
        $alias->assertRedirect();

        $aliasTarget = (string) $alias->headers->get('Location');

        expect($aliasTarget)->toMatch(
            '/pasax-[A-Za-z0-9]{12,32}\\/threat-map/',
        );

        $this->get(parse_url($aliasTarget, PHP_URL_PATH))->assertOk();
    },
);
