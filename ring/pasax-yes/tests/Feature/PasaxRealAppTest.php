<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Pasax\SecureMonitor\Concerns\HasPasaxSecurityRelations;
use Pasax\SecureMonitor\Models\BlockedIp;
use Pasax\SecureMonitor\Models\PasaxBlockedIp;
use Pasax\SecureMonitor\Models\PasaxLog;
use Pasax\SecureMonitor\Models\SecurityLog;
use Pasax\SecureMonitor\Rules\PasaxSafeAssetPath;
use Pasax\SecureMonitor\Rules\PasaxSafeImageFile;

uses(RefreshDatabase::class);

test(
    'user model uses HasPasaxSecurityRelations and works with relationships',
    function () {
        $traits = class_uses_recursive(User::class);

        expect(
            in_array(HasPasaxSecurityRelations::class, $traits, true),
        )->toBeTrue();

        $user = User::factory()->create([
            'name' => 'Security Admin',
            'email' => 'admin@example.com',
        ]);

        expect($user->logins())
            ->toBeInstanceOf(HasMany::class)
            ->and($user->trustedIps())
            ->toBeInstanceOf(HasMany::class)
            ->and($user->pasaxLogs())
            ->toBeInstanceOf(HasMany::class)
            ->and($user->securityLogs())
            ->toBeInstanceOf(HasMany::class)
            ->and($user->blockedIps())
            ->toBeInstanceOf(HasMany::class);
    },
);

test(
    'models resolve to pasax_ prefixed tables and legacy models alias transparently',
    function () {
        $blocked = PasaxBlockedIp::create([
            'ip_address' => '203.0.113.99',
            'reason' => 'Suspicious test attack',
            'is_active' => true,
        ]);

        expect($blocked->getTable())
            ->toBe('pasax_blocked_ips')
            ->and(PasaxBlockedIp::where('ip_address', '203.0.113.99')->exists())
            ->toBeTrue()
            ->and(BlockedIp::where('ip_address', '203.0.113.99')->exists())
            ->toBeTrue();

        $log = PasaxLog::create([
            'ip_address' => '203.0.113.99',
            'event_type' => 'threat_detected',
            'threat_level' => 'high',
            'rule_label' => 'SSTI probe',
        ]);

        expect($log->getTable())
            ->toBe('pasax_logs')
            ->and(PasaxLog::count())
            ->toBe(1)
            ->and(SecurityLog::count())
            ->toBe(1);
    },
);

test(
    'blocked ip receives 403 response through global WAF middleware',
    function () {
        PasaxBlockedIp::create([
            'ip_address' => '198.51.100.77',
            'reason' => 'Test block',
            'is_active' => true,
        ]);

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '198.51.100.77',
        ])->get('/');

        $response->assertStatus(403);
    },
);

test('dashboard overview route renders for authenticated users', function () {
    $user = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'superadmin@example.com',
    ]);

    $response = $this->actingAs($user)->followingRedirects()->get('/pasax');

    $response->assertStatus(200);
    $response->assertSee('sec-sidebar-version');
    $response->assertSee('v'.\Pasax\SecureMonitor\Services\PasaxService::VERSION);
});

test(
    'public client can submit unblock appeal ticket via rest api',
    function () {
        PasaxBlockedIp::create([
            'ip_address' => '198.51.100.88',
            'reason' => 'False positive block',
            'is_active' => true,
        ]);

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '198.51.100.88',
        ])->postJson('/api/pasax/unblock-tickets/submit', [
            'name' => 'Innocent User',
            'email' => 'innocent@example.com',
            'reason' => 'Saya adalah pengguna yang sah dan tidak sengaja terblokir.',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure(['success', 'ticket_number', 'message']);
    },
);

test(
    'waf threat detection engine inspects and blocks attack payload when instant block enabled',
    function () {
        config(['pasax.instant_block.enabled' => true]);

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '198.51.100.99',
        ])->get('/?probe=../../../../etc/passwd');

        $response->assertStatus(403);

        expect(PasaxLog::where('ip_address', '198.51.100.99')->exists())
            ->toBeTrue()
            ->and(
                PasaxBlockedIp::where('ip_address', '198.51.100.99')->exists(),
            )
            ->toBeTrue();
    },
);

test(
    'waf threat detection engine logs attack without blocking when instant block is disabled',
    function () {
        config([
            'pasax.instant_block.enabled' => false,
            'pasax.auto_block.enabled' => false,
        ]);

        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '198.51.100.101',
        ])->get('/?probe=../../../../etc/passwd');

        $response->assertStatus(200);

        expect(PasaxLog::where('ip_address', '198.51.100.101')->exists())
            ->toBeTrue()
            ->and(
                PasaxBlockedIp::where('ip_address', '198.51.100.101')->exists(),
            )
            ->toBeFalse();
    },
);

test('whitelisted ip is exempt from instant blocking', function () {
    config([
        'pasax.instant_block.enabled' => true,
        'pasax.whitelist' => ['198.51.100.200'],
    ]);

    $response = $this->withServerVariables([
        'REMOTE_ADDR' => '198.51.100.200',
    ])->get('/?probe=../../../../etc/passwd');

    $response->assertStatus(200);
    expect(
        PasaxBlockedIp::where('ip_address', '198.51.100.200')->exists(),
    )->toBeFalse();
});

test('artisan pasax:unblock-ip successfully unblocks an ip', function () {
    PasaxBlockedIp::create([
        'ip_address' => '198.51.100.222',
        'reason' => 'Admin test block',
        'is_active' => true,
    ]);

    $this->artisan('pasax:unblock-ip', [
        'ip' => '198.51.100.222',
    ])->assertSuccessful();

    expect(
        PasaxBlockedIp::where('ip_address', '198.51.100.222')
            ->where('is_active', true)
            ->exists(),
    )->toBeFalse();
});

test(
    'dashboard subpages render successfully for authenticated users',
    function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->followingRedirects()
            ->get('/pasax/logs')
            ->assertStatus(200);
        $this->actingAs($user)
            ->followingRedirects()
            ->get('/pasax/blocked-ips')
            ->assertStatus(200);
        $this->actingAs($user)
            ->followingRedirects()
            ->get('/pasax/tickets')
            ->assertStatus(200);
        $this->actingAs($user)
            ->followingRedirects()
            ->get('/pasax/server')
            ->assertStatus(200);
        $this->actingAs($user)
            ->followingRedirects()
            ->get('/pasax/settings')
            ->assertStatus(200);
    },
);

test(
    'safe image file and safe asset path validation rules function correctly',
    function () {
        $assetPathRule = new PasaxSafeAssetPath;
        $failCalled = false;
        $assetPathRule->validate(
            'icon',
            'icons/valid-badge.png',
            function () use (&$failCalled) {
                $failCalled = true;
            },
        );
        expect($failCalled)->toBeFalse();

        $failCalled = false;
        $assetPathRule->validate(
            'icon',
            'icons/shell.php.png',
            function () use (&$failCalled) {
                $failCalled = true;
            },
        );
        expect($failCalled)->toBeTrue();

        $safeImageRule = new PasaxSafeImageFile;
        $validImage = UploadedFile::fake()->image('avatar.jpg');
        $failCalled = false;
        $safeImageRule->validate('avatar', $validImage, function () use (
            &$failCalled,
        ) {
            $failCalled = true;
        });
        expect($failCalled)->toBeFalse();
    },
);
