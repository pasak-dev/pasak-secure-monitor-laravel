<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Pasax\SecureMonitor\Models\PasaxLog;
use Pasax\SecureMonitor\Models\PasaxSetting;
use Pasax\SecureMonitor\Services\ServerSecurityService;
use Pasax\SecureMonitor\Tests\TestUser;

beforeEach(function () {
    Cache::flush();
    app(ServerSecurityService::class)->forget();

    config()->set('pasax.dashboard.enabled', true);
    config()->set('pasax.dashboard.auth.enabled', false);

    // Pastikan view stubs terpasang untuk pengujian tampilan
    $target = resource_path('views/pages/pasax');
    File::ensureDirectoryExists($target);
    File::copyDirectory(
        dirname(__DIR__, 2).'/stubs/blade/pages/pasax',
        $target,
    );

    // Register routes/pasax-web-blade.php if not yet registered
    if (! Route::has('pasax.dashboard.server.report.export')) {
        require dirname(__DIR__, 2).'/routes/pasax-web-blade.php';
        Route::getRoutes()->refreshNameLookups();
    }
});

afterEach(function () {
    app(ServerSecurityService::class)->forget();
});

test('server security service generates comprehensive markdown audit report based on single date', function () {
    $service = app(ServerSecurityService::class);

    // Set server physical location
    PasaxSetting::setServerLocation('Pekanbaru, Riau (ID)', 0.5071, 101.4478, 'Asia/Jakarta');

    // Buat sampel log serangan pada tanggal 2026-11-15
    $targetDate = '2026-11-15';
    $log1 = new PasaxLog([
        'ip_address' => '198.51.100.44',
        'event_type' => 'sqli_detected',
        'threat_level' => 'critical',
        'method' => 'POST',
        'path' => '/api/v1/checkout',
        'rule_label' => 'SQL Injection Vector in Payload',
        'was_blocked' => true,
        'action_taken' => 'blocked_instant',
    ]);
    $log1->created_at = Carbon::parse('2026-11-15 10:15:30');
    $log1->save();

    $log2 = new PasaxLog([
        'ip_address' => '203.0.113.88',
        'event_type' => 'xss_payload',
        'threat_level' => 'high',
        'method' => 'GET',
        'path' => '/search',
        'rule_label' => 'Cross-Site Scripting Probe',
        'was_blocked' => false,
        'action_taken' => 'warn_logged',
    ]);
    $log2->created_at = Carbon::parse('2026-11-15 11:20:00');
    $log2->save();

    $report = $service->exportMarkdownReport($targetDate);

    expect($report)
        ->toBeString()
        ->toContain('# 🛡️ PASAX SECURE MONITOR — LAPORAN AUDIT PENTEST & SERVER HARDENING')
        ->toContain('2026-11-15')
        ->toContain('Pekanbaru, Riau (ID)')
        ->toContain('0.5071, 101.4478')
        ->toContain('Health Score')
        ->toContain('sqli_detected')
        ->toContain('198.51.100.44')
        ->toContain('Aplikasi & Konfigurasi')
        ->toContain('OWASP Top 10')
        ->toContain('Rencana Aksi & Rekomendasi Remediasi Prioritas')
        ->toContain('Audit Sign-Off');
});

test('server security service generates comprehensive date range audit report with daily recap', function () {
    $service = app(ServerSecurityService::class);

    // Buat sampel log serangan pada rentang tanggal: 2026-11-10 sampai 2026-11-12
    $logDay1 = new PasaxLog([
        'ip_address' => '198.51.100.11',
        'event_type' => 'path_traversal',
        'threat_level' => 'high',
        'method' => 'GET',
        'path' => '/download?file=../../etc/passwd',
        'rule_label' => 'Path Traversal Probe',
        'was_blocked' => true,
        'action_taken' => 'blocked_instant',
    ]);
    $logDay1->created_at = Carbon::parse('2026-11-10 08:30:00');
    $logDay1->save();

    $logDay2 = new PasaxLog([
        'ip_address' => '198.51.100.22',
        'event_type' => 'rce_attempt',
        'threat_level' => 'critical',
        'method' => 'POST',
        'path' => '/cgi-bin/test',
        'rule_label' => 'Remote Code Execution Attempt',
        'was_blocked' => true,
        'action_taken' => 'blocked_instant',
    ]);
    $logDay2->created_at = Carbon::parse('2026-11-11 14:15:00');
    $logDay2->save();

    $logDay3 = new PasaxLog([
        'ip_address' => '203.0.113.33',
        'event_type' => 'rate_limit_exceeded',
        'threat_level' => 'medium',
        'method' => 'POST',
        'path' => '/login',
        'rule_label' => 'Login Brute Force',
        'was_blocked' => false,
        'action_taken' => 'throttled',
    ]);
    $logDay3->created_at = Carbon::parse('2026-11-12 18:45:00');
    $logDay3->save();

    // Export laporan dengan date range: 2026-11-10 s/d 2026-11-12
    $report = $service->exportMarkdownReport('2026-11-10', '2026-11-12');

    expect($report)
        ->toBeString()
        ->toContain('2026-11-10 s/d 2026-11-12')
        ->toContain('(3 Hari)')
        ->toContain('Distribusi Tren Serangan Harian (Daily Attack Timeline)')
        ->toContain('2026-11-10')
        ->toContain('2026-11-11')
        ->toContain('2026-11-12')
        ->toContain('path_traversal')
        ->toContain('rce_attempt')
        ->toContain('rate_limit_exceeded')
        ->toContain('198.51.100.22');
});

test('admin can download date range markdown audit report via blade dashboard route', function () {
    $admin = TestUser::create([
        'name' => 'Lead Auditor Admin',
        'email' => 'auditor-range@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['pasax_dashboard_authenticated' => true])
        ->get('/pasax/server/report/export?start_date=2026-11-10&end_date=2026-11-15');

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
    expect($response->headers->get('Content-Disposition'))
        ->toContain('PASAX_Server_Security_Audit_Report_2026-11-10_sd_2026-11-15.md');

    expect($response->getContent())
        ->toContain('# 🛡️ PASAX SECURE MONITOR — LAPORAN AUDIT PENTEST & SERVER HARDENING')
        ->toContain('2026-11-10 s/d 2026-11-15')
        ->toContain('Lead Auditor Admin');
});

test('admin can download date range markdown audit report via api route', function () {
    $admin = TestUser::create([
        'name' => 'API Admin User',
        'email' => 'api-admin-range@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)
        ->get('/api/pasax/server/report/export?start_date=2026-11-10&end_date=2026-11-15');

    $response->assertSuccessful();
    $response->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
    expect($response->headers->get('Content-Disposition'))
        ->toContain('PASAX_Server_Security_Audit_Report_2026-11-10_sd_2026-11-15.md');
});

test('server audit blade view renders audit report export button and modal with range inputs', function () {
    $admin = TestUser::create([
        'name' => 'Dashboard Admin',
        'email' => 'dash-admin-range@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['pasax_dashboard_authenticated' => true])
        ->get('/pasax/server');

    $response->assertSuccessful();
    $response->assertSee('openReportExportModal');
    $response->assertSee('pasaxReportExportModal');
    $response->assertSee('modalStartDate');
    $response->assertSee('modalEndDate');
    $response->assertSee('setModalRange');
    $response->assertSee(__('Unduh Laporan Audit (.md)'));
});

test('sidebar renders dedicated audit reports menu item', function () {
    $admin = TestUser::create([
        'name' => 'Sidebar Admin',
        'email' => 'sidebar-admin-range@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['pasax_dashboard_authenticated' => true])
        ->get('/pasax/server');

    $response->assertSuccessful();
    $response->assertSee('/pasax/reports');
    $response->assertSee(__('Audit Reports'));
});

test('dedicated reports page renders date range picker, quick presets, and live preview', function () {
    $admin = TestUser::create([
        'name' => 'Reports Page Admin',
        'email' => 'reports-admin-range@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $response = $this->actingAs($admin)
        ->withSession(['pasax_dashboard_authenticated' => true])
        ->get('/pasax/reports?start_date=2026-11-10&end_date=2026-11-15');

    $response->assertSuccessful();
    $response->assertSee('reportStartDate');
    $response->assertSee('reportEndDate');
    $response->assertSee('2026-11-10');
    $response->assertSee('2026-11-15');
    $response->assertSee(__('Pratinjau Tampilan Rapi'));
    $response->assertSee(__('Format Mentah Markdown (.md)'));
    $response->assertSee('rawMarkdownTextarea');
    $response->assertSee('copyReportMarkdown');
    $response->assertSee('/pasax/server/report/export?start_date=2026-11-10', false);
    $response->assertSee('end_date=2026-11-15', false);
});
