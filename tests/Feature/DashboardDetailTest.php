<?php

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\BlockedIpController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\LogController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\TicketController;
use Pasax\SecureMonitor\Models\BlockedIp;
use Pasax\SecureMonitor\Models\IpUnblockRequest;
use Pasax\SecureMonitor\Models\SecurityLog;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

function publishBladeDashboard(): string
{
    $target = resource_path('views/pages/pasax');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasax',
            $target
        );
    }

    return $target;
}

function registerDashboardDummyRoutes(): void
{
    foreach ([
        'overview', 'logs', 'logs.show', 'logs.clear', 'logs.destroy',
        'blocked-ips', 'blocked-ips.show', 'blocked-ips.store', 'blocked-ips.toggle', 'blocked-ips.destroy',
        'server', 'sessions', 'tickets', 'tickets.show', 'tickets.respond', 'tickets.destroy', 'settings',
    ] as $name) {
        Route::get('/__pasax_detail_'.$name, fn () => 'ok')->name('pasax.dashboard.'.$name);
    }

    Route::getRoutes()->refreshNameLookups();
}

beforeEach(function () {
    config()->set('pasax.dashboard.driver', 'blade');
    publishBladeDashboard();
    registerDashboardDummyRoutes();
});

afterEach(function () {
    File::deleteDirectory(resource_path('views/pages/pasax'));
});

test('log detail renders evidence, context, and dark mode toggle', function () {
    $log = SecurityLog::create([
        'ip_address' => '203.0.113.10',
        'event_type' => 'sql_injection',
        'threat_level' => 'critical',
        'method' => 'GET',
        'path' => '/search?q=1',
        'full_url' => 'https://app.test/search?q=1',
        'rule_label' => 'SQLi payload',
        'evidence' => "' OR '1'='1",
        'user_agent' => 'EvilBot/1.0',
        'was_blocked' => true,
    ]);

    $response = app(LogController::class)->show($log->id);

    expect($response)->toBeInstanceOf(View::class)
        ->and($response->getName())->toBe('pages.pasax.log-detail');

    $html = $response->render();

    expect($html)->toContain('Log entry #')
        ->and($html)->toContain(htmlspecialchars("' OR '1'='1", ENT_QUOTES))
        ->and($html)->toContain('EvilBot/1.0')
        ->and($html)->toContain('Back to logs')
        ->and($html)->toContain(__('Negara Penyerang'))
        ->and($html)->toContain(__('Provinsi / Wilayah'))
        ->and($html)->toContain('data-sec-theme-toggle')
        ->and($html)->toContain('pasax-theme');
});

test('log detail aborts with 404 for missing entries', function () {
    expect(fn () => app(LogController::class)->show(999999))
        ->toThrow(NotFoundHttpException::class);
});

test('blocked ip detail renders reason, related logs, and actions', function () {
    $block = BlockedIp::create([
        'ip_address' => '198.51.100.77',
        'reason' => 'Brute force login',
        'notes' => 'Repeated attempts',
        'is_active' => true,
    ]);
    SecurityLog::create([
        'ip_address' => '198.51.100.77',
        'event_type' => 'login_throttle',
        'threat_level' => 'high',
        'path' => '/login',
    ]);

    $response = app(BlockedIpController::class)->show($block->id);

    expect($response)->toBeInstanceOf(View::class)
        ->and($response->getName())->toBe('pages.pasax.blocked-ip-detail');

    $html = $response->render();

    expect($html)->toContain('Brute force login')
        ->and($html)->toContain('Repeated attempts')
        ->and($html)->toContain('Triggering logs')
        ->and($html)->toContain(__('Negara Penyerang'))
        ->and($html)->toContain(__('Provinsi / Wilayah'))
        ->and($html)->toContain('Disable block');
});

test('blocked ip detail aborts with 404 for missing entries', function () {
    expect(fn () => app(BlockedIpController::class)->show(999999))
        ->toThrow(NotFoundHttpException::class);
});

test('ticket detail renders pending appeal with respond form', function () {
    $ticket = IpUnblockRequest::create([
        'ticket_number' => 'TKT-D1',
        'ip_address' => '203.0.113.20',
        'name' => 'Jane',
        'email' => 'jane@example.com',
        'reason' => 'Please unblock my office IP.',
        'status' => 'pending',
    ]);

    $response = app(TicketController::class)->show($ticket->id);

    expect($response)->toBeInstanceOf(View::class)
        ->and($response->getName())->toBe('pages.pasax.ticket-detail');

    $html = $response->render();

    expect($html)->toContain('TKT-D1')
        ->and($html)->toContain('Please unblock my office IP.')
        ->and($html)->toContain('Approve &amp; unblock')
        ->and($html)->toContain('name="action" value="approve"')
        ->and($html)->toContain('name="action" value="reject"');
});

test('ticket detail aborts with 404 for missing entries', function () {
    expect(fn () => app(TicketController::class)->show(999999))
        ->toThrow(NotFoundHttpException::class);
});

test('internal dashboard route file exposes the detail routes', function () {
    $blade = File::get(__DIR__.'/../../routes/pasax-web-blade.php');

    expect($blade)->toContain('logs.show')
        ->and($blade)->toContain('blocked-ips.show')
        ->and($blade)->toContain('tickets.show');
});

test('published blade route variant exposes the detail routes', function () {
    $bladeVariant = File::get(__DIR__.'/../../stubs/routes/pasax-blade.php.stub');

    expect($bladeVariant)->toContain("->name('logs.show')")
        ->and($bladeVariant)->toContain("->name('tickets.show')")
        ->and($bladeVariant)->not->toContain('Route::livewire')
        ->and($bladeVariant)->not->toContain('Inertia');
});

test('blade layout ships the dark mode toggle and detail styles', function () {
    $bladeLayout = File::get(__DIR__.'/../../stubs/blade/pages/pasax/layout.blade.php');

    expect($bladeLayout)->toContain('data-sec-theme-toggle')
        ->and($bladeLayout)->toContain('sec-code-block')
        ->and($bladeLayout)->toContain('color-scheme')
        ->and($bladeLayout)->toContain('sec-detail-grid')
        ->and($bladeLayout)->toContain('sec-sidebar-version')
        ->and($bladeLayout)->toContain('\Pasax\SecureMonitor\Services\PasaxService::VERSION');
});

test('blade detail pages exist for logs, blocks, and tickets', function () {
    foreach (['log-detail.blade.php', 'blocked-ip-detail.blade.php', 'ticket-detail.blade.php'] as $page) {
        expect(File::exists(__DIR__.'/../../stubs/blade/pages/pasax/'.$page))->toBeTrue();
    }

    expect(File::get(__DIR__.'/../../stubs/blade/pages/pasax/log-detail.blade.php'))->toContain('sec-code-block')
        ->and(File::get(__DIR__.'/../../stubs/blade/pages/pasax/ticket-detail.blade.php'))->toContain('Approve &');
});

test('blocked ip controller accepts string ID for toggle and destroy', function () {
    $block = BlockedIp::create([
        'ip_address' => '198.51.100.99',
        'reason' => 'Testing toggle with string id',
        'is_active' => true,
    ]);

    $response = app(BlockedIpController::class)->toggle((string) $block->id);
    expect($response)->toBeInstanceOf(RedirectResponse::class);

    $block->refresh();
    expect($block->is_active)->toBeFalse();

    app(BlockedIpController::class)->toggle((string) $block->id);
    $block->refresh();
    expect($block->is_active)->toBeTrue();

    $destroyResponse = app(BlockedIpController::class)->destroy((string) $block->id);
    expect($destroyResponse)->toBeInstanceOf(RedirectResponse::class);
    expect(BlockedIp::query()->find($block->id))->toBeNull();
});
