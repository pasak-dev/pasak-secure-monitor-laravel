<?php

use Illuminate\Auth\Events\Failed;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Pasak\SecureMonitor\Models\PasakBlockedIp;
use Pasak\SecureMonitor\Models\PasakLog;
use Pasak\SecureMonitor\Models\PasakLoginAttempt;
use Pasak\SecureMonitor\Services\PasakLoginThrottleService;
use Pasak\SecureMonitor\Services\PasakService;

beforeEach(function () {
    config()->set('pasak.enabled', true);
    config()->set('pasak.block_enforcement', true);
    config()->set('pasak.auto_block.enabled', true);
    config()->set('pasak.login_lockout.enabled', true);
    config()->set('pasak.login_lockout.threshold', 3);
    config()->set('pasak.login_lockout.step1_seconds', 30);
    config()->set('pasak.login_lockout.step2_seconds', 60);
    config()->set('pasak.login_lockout.step3_seconds', 120);
    config()->set('pasak.login_lockout.max_seconds', 3600);
    config()->set('pasak.login_lockout.decay_minutes', 360);
});

test('failed login attempts do not trigger WAF hard IP block 403', function () {
    $service = app(PasakService::class);
    $throttle = app(PasakLoginThrottleService::class);
    $ip = '198.51.100.55';

    // Simulate 5 failed logins
    for ($i = 0; $i < 5; $i++) {
        $throttle->registerFailure('target@user.test', $ip);
    }

    // WAF auto block should not be tripped
    expect($service->autoBlockIfNeeded($ip))->toBeNull()
        ->and(PasakBlockedIp::query()->where('ip_address', $ip)->exists())->toBeFalse();

    // No WAF critical/medium threats from login events
    expect(PasakLog::query()->where('ip_address', $ip)->whereIn('threat_level', ['high', 'critical'])->count())->toBe(0);
});

test('stepped cooldown applies 30s -> 60s -> 120s sequence', function () {
    $throttle = app(PasakLoginThrottleService::class);
    $email = 'john@example.test';
    $ip = '198.51.100.56';

    // Attempt 1
    $s1 = $throttle->registerFailure($email, $ip);
    expect($s1['locked'])->toBeFalse()
        ->and($s1['attempts'])->toBe(1)
        ->and($s1['level'])->toBe(0);

    // Attempt 2
    $s2 = $throttle->registerFailure($email, $ip);
    expect($s2['locked'])->toBeFalse()
        ->and($s2['attempts'])->toBe(2)
        ->and($s2['level'])->toBe(0);

    // Attempt 3 (Threshold reached -> Level 1 lockout, 30 seconds)
    $s3 = $throttle->registerFailure($email, $ip);
    expect($s3['locked'])->toBeTrue()
        ->and($s3['level'])->toBe(1)
        ->and($s3['seconds'])->toBeGreaterThanOrEqual(29)->toBeLessThanOrEqual(30)
        ->and($throttle->isIpLocked($ip))->toBeTrue();

    // Fast-forward past 30 seconds
    Carbon::setTestNow(now()->addSeconds(31));

    expect($throttle->status($email, $ip)['locked'])->toBeFalse();

    // Next failure trips Stage 2 (60 seconds)
    $s4 = $throttle->registerFailure($email, $ip);
    expect($s4['locked'])->toBeTrue()
        ->and($s4['level'])->toBe(2)
        ->and($s4['seconds'])->toBeGreaterThanOrEqual(59)->toBeLessThanOrEqual(60);

    // Fast-forward past 60 seconds
    Carbon::setTestNow(now()->addSeconds(61));

    // Next failure trips Stage 3 (120 seconds)
    $s5 = $throttle->registerFailure($email, $ip);
    expect($s5['locked'])->toBeTrue()
        ->and($s5['level'])->toBe(3)
        ->and($s5['seconds'])->toBeGreaterThanOrEqual(119)->toBeLessThanOrEqual(120);

    Carbon::setTestNow(null);
});

test('penalty cycle decays and returns to normal after 6 hours of inactivity', function () {
    $throttle = app(PasakLoginThrottleService::class);
    $email = 'decay@user.test';
    $ip = '198.51.100.57';

    // Reach level 1
    $throttle->registerFailure($email, $ip);
    $throttle->registerFailure($email, $ip);
    $status = $throttle->registerFailure($email, $ip);
    expect($status['locked'])->toBeTrue()
        ->and($status['level'])->toBe(1);

    // Fast forward 5 hours (less than 6 hours decay window)
    Carbon::setTestNow(now()->addHours(5));

    $status5h = $throttle->status($email, $ip);
    expect($status5h['locked'])->toBeFalse()
        ->and($status5h['level'])->toBe(1); // Level preserved within 6 hour window

    // Fast forward past 6 hours (e.g. 6.5 hours from original attempt)
    Carbon::setTestNow(now()->addMinutes(100)); // Total 6 hours 40 minutes

    $statusAfterDecay = $throttle->status($email, $ip);
    expect($statusAfterDecay['locked'])->toBeFalse()
        ->and($statusAfterDecay['level'])->toBe(0)
        ->and($statusAfterDecay['attempts'])->toBe(0);

    Carbon::setTestNow(null);
});

test('successful login immediately resets failure count and level to normal', function () {
    $throttle = app(PasakLoginThrottleService::class);
    $email = 'reset@user.test';
    $ip = '198.51.100.58';

    $throttle->registerFailure($email, $ip);
    $throttle->registerFailure($email, $ip);
    $throttle->registerFailure($email, $ip);

    expect(PasakLoginAttempt::query()->where('email', $email)->exists())->toBeTrue();

    // User succeeds login
    $throttle->registerSuccess($email, $ip);

    expect(PasakLoginAttempt::query()->where('email', $email)->exists())->toBeFalse();
    expect($throttle->status($email, $ip)['level'])->toBe(0);
});

test('renderLockoutResponse returns 429 Too Many Requests with countdown details', function () {
    $throttle = app(PasakLoginThrottleService::class);
    $status = [
        'locked' => true,
        'seconds' => 30,
        'level' => 1,
        'attempts' => 0,
        'threshold' => 3,
        'message' => 'Akun dikunci sementara 30 detik.',
        'email' => 'locked@user.test',
    ];

    // JSON API request
    $jsonReq = Request::create('/api/login', 'POST', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
    $jsonResp = $throttle->renderLockoutResponse($jsonReq, $status);

    expect($jsonResp->getStatusCode())->toBe(429)
        ->and($jsonResp->headers->get('Retry-After'))->toBe('30');

    $data = json_decode($jsonResp->getContent(), true);
    expect($data['locked'])->toBeTrue()
        ->and($data['seconds_remaining'])->toBe(30)
        ->and($data['lockout_level'])->toBe(1);

    // Web request
    $webReq = Request::create('/login', 'POST');
    $webResp = $throttle->renderLockoutResponse($webReq, $status);

    expect($webResp->getStatusCode())->toBe(429)
        ->and($webResp->headers->get('Retry-After'))->toBe('30')
        ->and($webResp->getContent())->toContain('30')
        ->and($webResp->getContent())->toContain('Akses Login Dibatasi');
});

test('admin can update stepped cooldown settings and decay period', function () {
    $service = app(PasakService::class);

    $service->updateSettings([
        'login_lockout_step1_seconds' => 45,
        'login_lockout_step2_seconds' => 90,
        'login_lockout_step3_seconds' => 180,
        'login_lockout_decay_minutes' => 720, // 12 hours
        'login_lockout_threshold' => 2,
    ]);

    $throttle = app(PasakLoginThrottleService::class);

    expect($throttle->secondsForLevel(1))->toBe(45)
        ->and($throttle->secondsForLevel(2))->toBe(90)
        ->and($throttle->secondsForLevel(3))->toBe(180)
        ->and($service->getSettings()['login_lockout_decay_minutes'])->toBe(720)
        ->and($service->getSettings()['login_lockout_threshold'])->toBe(2);
});
