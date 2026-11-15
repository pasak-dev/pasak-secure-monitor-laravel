<?php

use Illuminate\Support\Facades\Http;
use Pasax\SecureMonitor\Services\PasaxCloudService;

beforeEach(function () {
    config()->set('app.name', 'Laravel');
    config()->set('app.url', 'http://localhost:8000');
    config()->set('pasax.cloud.enabled', true);
    config()->set('pasax.cloud.key', 'psk_live_test1234567890abcdef');
    config()->set('pasax.cloud.url', 'https://mock-cloud.pasax.test');
});

test('cloud service extracts metadata from APP_NAME and APP_URL', function () {
    $cloud = app(PasaxCloudService::class);
    $meta = $cloud->getAppMetadata();

    expect($meta['name'])->toBe('Laravel')
        ->and($meta['url'])->toBe('http://localhost:8000')
        ->and($meta['host'])->toBe('localhost')
        ->and($meta['is_https'])->toBeFalse();
});

test('cloud service validates isConfigured based on key format', function () {
    $cloud = app(PasaxCloudService::class);
    expect($cloud->isConfigured())->toBeTrue();
    expect($cloud->getMaskedKey())->toContain('psk_live');

    config()->set('pasax.cloud.key', null);
    $emptyCloud = new PasaxCloudService;
    expect($emptyCloud->isConfigured())->toBeFalse();
});

test('cloud service can send heartbeat and report threat event', function () {
    Http::fake([
        'https://mock-cloud.pasax.test/api/v1/nodes/telemetry' => Http::response([
            'status' => 'acknowledged',
            'node_id' => 'nod_mock_123',
        ], 200),
        'https://mock-cloud.pasax.test/api/v1/events/threat' => Http::response([
            'status' => 'success',
            'event_id' => 'evt_123',
        ], 200),
        'https://mock-cloud.pasax.test/api/v1/threats/global-blocklist' => Http::response([
            'ips' => ['198.51.100.99', '203.0.113.88'],
        ], 200),
    ]);

    $cloud = app(PasaxCloudService::class);

    $hb = $cloud->sendHeartbeat();
    expect($hb['success'])->toBeTrue()
        ->and($hb['app_name'])->toBe('Laravel')
        ->and($hb['app_url'])->toBe('http://localhost:8000');

    $reported = $cloud->reportThreat([
        'event_type' => 'sql_injection',
        'ip_address' => '198.51.100.42',
        'threat_level' => 'critical',
    ]);
    expect($reported)->toBeTrue();

    $blocklist = $cloud->fetchGlobalBlocklist();
    expect($blocklist)->toHaveCount(2)
        ->and($blocklist)->toContain('198.51.100.99');
});
