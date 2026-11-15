<?php

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Pasax\SecureMonitor\Concerns\HasPasaxSecurityRelations;
use Pasax\SecureMonitor\Concerns\HasSecurityRelations;
use Pasax\SecureMonitor\Concerns\ResolvesPasaxTable;
use Pasax\SecureMonitor\Console\Commands\PasaxBaselineCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxInstallCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxPruneSecurityLogsCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxPurgeInjectedDataCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxScanAccessLogsCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxUnblockIpCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxUninstallCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxUpgradeCommand;
use Pasax\SecureMonitor\Console\Commands\PruneSecurityLogs;
use Pasax\SecureMonitor\Console\Commands\PurgeInjectedData;
use Pasax\SecureMonitor\Console\Commands\SecurityBaselineCommand;
use Pasax\SecureMonitor\Console\Commands\SecurityInstallCommand;
use Pasax\SecureMonitor\Console\Commands\SecurityScanAccessLogs;
use Pasax\SecureMonitor\Console\Commands\SecurityUpgradeCommand;
use Pasax\SecureMonitor\Console\Commands\UnblockIpAddress;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxAllowedIpApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxBlockedIpApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxIpUnblockRequestApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxSecurityLogApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxSecuritySettingApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxServerSecurityApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxUserSessionApiController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxAllowedIpController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxBlockedIpController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxLogController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxOverviewController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxServerController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxSessionController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxSettingController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxTicketController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\RespondsWithDashboard;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\RespondsWithPasaxDashboard;
use Pasax\SecureMonitor\Http\Middleware\BlockIpAddress;
use Pasax\SecureMonitor\Http\Middleware\DetectSecurityThreats;
use Pasax\SecureMonitor\Http\Middleware\EnsureSecurityAdmin;
use Pasax\SecureMonitor\Http\Middleware\InjectAnnouncementBanner;
use Pasax\SecureMonitor\Http\Middleware\PasaxBlockIpAddress;
use Pasax\SecureMonitor\Http\Middleware\PasaxDetectSecurityThreats;
use Pasax\SecureMonitor\Http\Middleware\PasaxEnsureSecurityAdmin;
use Pasax\SecureMonitor\Http\Middleware\PasaxInjectAnnouncementBanner;
use Pasax\SecureMonitor\Http\Middleware\PasaxTrackUserActivity;
use Pasax\SecureMonitor\Http\Middleware\TrackUserActivity;
use Pasax\SecureMonitor\Listeners\LogFailedLoginAttempt;
use Pasax\SecureMonitor\Listeners\PasaxLogFailedLoginAttempt;
use Pasax\SecureMonitor\Listeners\PasaxRecordUserLogin;
use Pasax\SecureMonitor\Listeners\PasaxResetLoginAttempts;
use Pasax\SecureMonitor\Listeners\RecordUserLogin;
use Pasax\SecureMonitor\Listeners\ResetLoginAttempts;
use Pasax\SecureMonitor\Models\AllowedIp;
use Pasax\SecureMonitor\Models\BlockedIp;
use Pasax\SecureMonitor\Models\IpUnblockRequest;
use Pasax\SecureMonitor\Models\LoginAttempt;
use Pasax\SecureMonitor\Models\PasaxAllowedIp;
use Pasax\SecureMonitor\Models\PasaxBlockedIp;
use Pasax\SecureMonitor\Models\PasaxIpUnblockRequest;
use Pasax\SecureMonitor\Models\PasaxLog;
use Pasax\SecureMonitor\Models\PasaxLoginAttempt;
use Pasax\SecureMonitor\Models\PasaxSetting;
use Pasax\SecureMonitor\Models\PasaxTrustedIp;
use Pasax\SecureMonitor\Models\PasaxUserLogin;
use Pasax\SecureMonitor\Models\SecurityLog;
use Pasax\SecureMonitor\Models\SecuritySetting;
use Pasax\SecureMonitor\Models\TrustedIp;
use Pasax\SecureMonitor\Models\UserLogin;
use Pasax\SecureMonitor\Rules\PasaxSafeAssetPath;
use Pasax\SecureMonitor\Rules\PasaxSafeImageFile;
use Pasax\SecureMonitor\Rules\SafeAssetPath;
use Pasax\SecureMonitor\Rules\SafeImageFile;
use Pasax\SecureMonitor\Services\AccessLogScannerService;
use Pasax\SecureMonitor\Services\LoginThrottleService;
use Pasax\SecureMonitor\Services\PasaxAccessLogScannerService;
use Pasax\SecureMonitor\Services\PasaxLoginThrottleService;
use Pasax\SecureMonitor\Services\PasaxServerSecurityService;
use Pasax\SecureMonitor\Services\PasaxService;
use Pasax\SecureMonitor\Services\PasaxUserLoginService;
use Pasax\SecureMonitor\Services\PasaxVersionCheckService;
use Pasax\SecureMonitor\Services\ServerSecurityService;
use Pasax\SecureMonitor\Services\UserLoginService;
use Pasax\SecureMonitor\Services\VersionCheckService;
use Pasax\SecureMonitor\Support\DashboardView;
use Pasax\SecureMonitor\Support\PasaxDashboardView;
use Pasax\SecureMonitor\Support\PasaxTableResolver;
use Pasax\SecureMonitor\Support\TableResolver;

afterEach(function () {
    ResolvesPasaxTable::clearResolvedTableCache();
});

test('all pasax models have pasax_ prefix by default', function () {
    ResolvesPasaxTable::clearResolvedTableCache();

    expect((new PasaxBlockedIp)->getTable())->toBe('pasax_blocked_ips')
        ->and((new PasaxLog)->getTable())->toBe('pasax_logs')
        ->and((new PasaxLoginAttempt)->getTable())->toBe('pasax_login_attempts')
        ->and((new PasaxIpUnblockRequest)->getTable())->toBe('pasax_ip_unblock_requests')
        ->and((new PasaxUserLogin)->getTable())->toBe('pasax_user_logins')
        ->and((new PasaxTrustedIp)->getTable())->toBe('pasax_trusted_ips')
        ->and((new PasaxAllowedIp)->getTable())->toBe('pasax_allowed_ips')
        ->and((new PasaxSetting)->getTable())->toBe('pasax_settings');
});

test('legacy model classes are transparent aliases of the prefixed models', function () {
    expect(class_exists(BlockedIp::class))->toBeTrue()
        ->and(class_exists(SecurityLog::class))->toBeTrue()
        ->and(class_exists(LoginAttempt::class))->toBeTrue()
        ->and(class_exists(IpUnblockRequest::class))->toBeTrue()
        ->and(class_exists(UserLogin::class))->toBeTrue()
        ->and(class_exists(TrustedIp::class))->toBeTrue()
        ->and(class_exists(AllowedIp::class))->toBeTrue()
        ->and(class_exists(SecuritySetting::class))->toBeTrue();

    $blocked = new BlockedIp(['ip_address' => '1.2.3.4']);
    expect($blocked)->toBeInstanceOf(PasaxBlockedIp::class)
        ->and($blocked)->toBeInstanceOf(BlockedIp::class)
        ->and($blocked->getTable())->toBe('pasax_blocked_ips');

    $log = new SecurityLog(['ip_address' => '1.2.3.4', 'threat_level' => 'high']);
    expect($log)->toBeInstanceOf(PasaxLog::class)
        ->and($log)->toBeInstanceOf(SecurityLog::class)
        ->and($log->getTable())->toBe('pasax_logs');
});

test('user relations support both legacy and new relation names', function () {
    $user = $this->createRegularUser();

    expect($user->securityLogs())->toBeInstanceOf(HasMany::class)
        ->and($user->pasaxLogs())->toBeInstanceOf(HasMany::class)
        ->and($user->blockedIps())->toBeInstanceOf(HasMany::class)
        ->and($user->logins())->toBeInstanceOf(HasMany::class)
        ->and($user->trustedIps())->toBeInstanceOf(HasMany::class)
        ->and($user->allowedIps())->toBeInstanceOf(HasMany::class)
        ->and($user->resolvedTickets())->toBeInstanceOf(HasMany::class);
});

test('resolvesPasaxTable transparently falls back to legacy table when prefixed table is not yet migrated', function () {
    ResolvesPasaxTable::clearResolvedTableCache();

    // Temporarily simulate pre-migration state where only legacy 'blocked_ips' exists
    Schema::rename('pasax_blocked_ips', 'blocked_ips');

    try {
        ResolvesPasaxTable::clearResolvedTableCache();
        $model = new PasaxBlockedIp;
        expect($model->getTable())->toBe('blocked_ips');

        // Querying should work on legacy table without throwing table not found error
        BlockedIp::create(['ip_address' => '10.0.0.99', 'source' => 'manual', 'is_active' => true]);
        expect(DB::table('blocked_ips')->where('ip_address', '10.0.0.99')->exists())->toBeTrue()
            ->and(BlockedIp::where('ip_address', '10.0.0.99')->exists())->toBeTrue();
    } finally {
        if (Schema::hasTable('blocked_ips') && ! Schema::hasTable('pasax_blocked_ips')) {
            Schema::rename('blocked_ips', 'pasax_blocked_ips');
        }
        ResolvesPasaxTable::clearResolvedTableCache();
    }
});

test('resolvesPasaxTable resolves to prefixed table even if user config still has legacy table name', function () {
    ResolvesPasaxTable::clearResolvedTableCache();
    config()->set('pasax.table_names.blocked_ips', 'blocked_ips');

    // DB has pasax_blocked_ips, but config says blocked_ips
    $model = new PasaxBlockedIp;
    expect($model->getTable())->toBe('pasax_blocked_ips');
});

test('resolvesPasaxTable strictly respects custom user-defined table names', function () {
    ResolvesPasaxTable::clearResolvedTableCache();
    config()->set('pasax.table_names.blocked_ips', 'my_custom_firewall_table');

    $model = new PasaxBlockedIp;
    expect($model->getTable())->toBe('my_custom_firewall_table');
});

test('prefix migration safely migrates existing legacy tables preserving data', function () {
    // 1. Setup legacy tables with dummy records
    Schema::disableForeignKeyConstraints();
    foreach ([
        'pasax_blocked_ips' => 'blocked_ips',
        'pasax_login_attempts' => 'login_attempts',
        'pasax_ip_unblock_requests' => 'ip_unblock_requests',
        'pasax_user_logins' => 'user_logins',
        'pasax_trusted_ips' => 'trusted_ips',
        'pasax_allowed_ips' => 'allowed_ips',
    ] as $new => $old) {
        if (Schema::hasTable($new) && ! Schema::hasTable($old)) {
            Schema::rename($new, $old);
        }
    }
    Schema::enableForeignKeyConstraints();

    DB::table('blocked_ips')->insert([
        'ip_address' => '192.168.100.50',
        'source' => 'manual',
        'is_active' => 1,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // 2. Run the prefix migration
    $migration = require __DIR__.'/../../database/migrations/2026_10_21_000001_prefix_pasax_tables.php';
    $migration->up();

    // 3. Verify tables were renamed to pasax_* and data is preserved
    expect(Schema::hasTable('pasax_blocked_ips'))->toBeTrue()
        ->and(Schema::hasTable('blocked_ips'))->toBeFalse()
        ->and(Schema::hasTable('pasax_login_attempts'))->toBeTrue()
        ->and(Schema::hasTable('login_attempts'))->toBeFalse()
        ->and(Schema::hasTable('pasax_ip_unblock_requests'))->toBeTrue()
        ->and(Schema::hasTable('ip_unblock_requests'))->toBeFalse()
        ->and(Schema::hasTable('pasax_user_logins'))->toBeTrue()
        ->and(Schema::hasTable('user_logins'))->toBeFalse()
        ->and(Schema::hasTable('pasax_trusted_ips'))->toBeTrue()
        ->and(Schema::hasTable('trusted_ips'))->toBeFalse()
        ->and(Schema::hasTable('pasax_allowed_ips'))->toBeTrue()
        ->and(Schema::hasTable('allowed_ips'))->toBeFalse();

    $preserved = DB::table('pasax_blocked_ips')->where('ip_address', '192.168.100.50')->first();
    expect($preserved)->not->toBeNull()
        ->and($preserved->ip_address)->toBe('192.168.100.50');

    // 4. Test rollback (down)
    $migration->down();
    expect(Schema::hasTable('blocked_ips'))->toBeTrue()
        ->and(Schema::hasTable('pasax_blocked_ips'))->toBeFalse();

    // Clean up back to normal state
    $migration->up();
    expect(Schema::hasTable('pasax_blocked_ips'))->toBeTrue();
});

test('upgrade command migrates legacy table_names in config/pasax.php', function () {
    $tempDir = sys_get_temp_dir().'/pasax_test_upgrade_'.uniqid();
    File::makeDirectory($tempDir.'/config', 0755, true);
    $this->app->setBasePath($tempDir);

    $oldConfig = <<<'PHP'
<?php

return [
    'table_names' => [
        'users' => env('PASAX_USERS_TABLE', env('SECURITY_USERS_TABLE', 'users')),
        'blocked_ips' => env('PASAX_BLOCKED_IPS_TABLE', env('SECURITY_BLOCKED_IPS_TABLE', 'blocked_ips')),
        'pasax_logs' => env('PASAX_LOGS_TABLE', env('SECURITY_LOGS_TABLE', 'pasax_logs')),
        'login_attempts' => env('PASAX_LOGIN_ATTEMPTS_TABLE', env('SECURITY_LOGIN_ATTEMPTS_TABLE', 'login_attempts')),
        'ip_unblock_requests' => env('PASAX_IP_UNBLOCK_REQUESTS_TABLE', env('SECURITY_IP_UNBLOCK_REQUESTS_TABLE', 'ip_unblock_requests')),
        'user_logins' => env('PASAX_USER_LOGINS_TABLE', env('SECURITY_USER_LOGINS_TABLE', 'user_logins')),
        'trusted_ips' => env('PASAX_TRUSTED_IPS_TABLE', env('SECURITY_TRUSTED_IPS_TABLE', 'trusted_ips')),
        'allowed_ips' => env('PASAX_ALLOWED_IPS_TABLE', 'allowed_ips'),
        'settings' => env('PASAX_SETTINGS_TABLE', 'pasax_settings'),
    ],
];
PHP;
    File::put($tempDir.'/config/pasax.php', $oldConfig);

    $this->artisan('pasax:upgrade', [
        '--force' => true,
        '--no-composer' => true,
        '--no-migrate' => true,
    ])->assertSuccessful();

    $migrated = File::get($tempDir.'/config/pasax.php');
    expect($migrated)->toContain("'blocked_ips' => env('PASAX_BLOCKED_IPS_TABLE', env('SECURITY_BLOCKED_IPS_TABLE', 'pasax_blocked_ips'))")
        ->and($migrated)->toContain("'login_attempts' => env('PASAX_LOGIN_ATTEMPTS_TABLE', env('SECURITY_LOGIN_ATTEMPTS_TABLE', 'pasax_login_attempts'))")
        ->and($migrated)->toContain("'ip_unblock_requests' => env('PASAX_IP_UNBLOCK_REQUESTS_TABLE', env('SECURITY_IP_UNBLOCK_REQUESTS_TABLE', 'pasax_ip_unblock_requests'))")
        ->and($migrated)->toContain("'user_logins' => env('PASAX_USER_LOGINS_TABLE', env('SECURITY_USER_LOGINS_TABLE', 'pasax_user_logins'))")
        ->and($migrated)->toContain("'trusted_ips' => env('PASAX_TRUSTED_IPS_TABLE', env('SECURITY_TRUSTED_IPS_TABLE', 'pasax_trusted_ips'))")
        ->and($migrated)->toContain("'allowed_ips' => env('PASAX_ALLOWED_IPS_TABLE', 'pasax_allowed_ips')");

    File::deleteDirectory($tempDir);
});

test('all pasax middleware, listeners, rules, services, commands, controllers are prefixed with Pasax', function () {
    // Middleware
    expect(class_exists(PasaxBlockIpAddress::class))->toBeTrue()
        ->and(class_exists(PasaxDetectSecurityThreats::class))->toBeTrue()
        ->and(class_exists(PasaxEnsureSecurityAdmin::class))->toBeTrue()
        ->and(class_exists(PasaxInjectAnnouncementBanner::class))->toBeTrue()
        ->and(class_exists(PasaxTrackUserActivity::class))->toBeTrue();

    // Middleware legacy aliases
    expect(class_exists(BlockIpAddress::class))->toBeTrue()
        ->and(class_exists(DetectSecurityThreats::class))->toBeTrue()
        ->and(class_exists(EnsureSecurityAdmin::class))->toBeTrue()
        ->and(class_exists(InjectAnnouncementBanner::class))->toBeTrue()
        ->and(class_exists(TrackUserActivity::class))->toBeTrue();

    // Listeners
    expect(class_exists(PasaxLogFailedLoginAttempt::class))->toBeTrue()
        ->and(class_exists(PasaxRecordUserLogin::class))->toBeTrue()
        ->and(class_exists(PasaxResetLoginAttempts::class))->toBeTrue();

    // Listeners legacy aliases
    expect(class_exists(LogFailedLoginAttempt::class))->toBeTrue()
        ->and(class_exists(RecordUserLogin::class))->toBeTrue()
        ->and(class_exists(ResetLoginAttempts::class))->toBeTrue();

    // Rules
    expect(class_exists(PasaxSafeAssetPath::class))->toBeTrue()
        ->and(class_exists(PasaxSafeImageFile::class))->toBeTrue()
        ->and(class_exists(SafeAssetPath::class))->toBeTrue()
        ->and(class_exists(SafeImageFile::class))->toBeTrue();

    // Services
    expect(class_exists(PasaxService::class))->toBeTrue()
        ->and(class_exists(PasaxAccessLogScannerService::class))->toBeTrue()
        ->and(class_exists(PasaxLoginThrottleService::class))->toBeTrue()
        ->and(class_exists(PasaxServerSecurityService::class))->toBeTrue()
        ->and(class_exists(PasaxUserLoginService::class))->toBeTrue()
        ->and(class_exists(PasaxVersionCheckService::class))->toBeTrue();

    // Services legacy aliases
    expect(class_exists(AccessLogScannerService::class))->toBeTrue()
        ->and(class_exists(LoginThrottleService::class))->toBeTrue()
        ->and(class_exists(ServerSecurityService::class))->toBeTrue()
        ->and(class_exists(UserLoginService::class))->toBeTrue()
        ->and(class_exists(VersionCheckService::class))->toBeTrue();

    // Support
    expect(class_exists(PasaxDashboardView::class))->toBeTrue()
        ->and(class_exists(PasaxTableResolver::class))->toBeTrue()
        ->and(class_exists(DashboardView::class))->toBeTrue()
        ->and(class_exists(TableResolver::class))->toBeTrue();

    // Concerns / Traits
    expect(trait_exists(HasPasaxSecurityRelations::class))->toBeTrue()
        ->and(trait_exists(HasSecurityRelations::class))->toBeTrue()
        ->and(trait_exists(RespondsWithPasaxDashboard::class))->toBeTrue()
        ->and(trait_exists(RespondsWithDashboard::class))->toBeTrue();

    // Commands
    expect(class_exists(PasaxInstallCommand::class))->toBeTrue()
        ->and(class_exists(PasaxUpgradeCommand::class))->toBeTrue()
        ->and(class_exists(PasaxBaselineCommand::class))->toBeTrue()
        ->and(class_exists(PasaxPruneSecurityLogsCommand::class))->toBeTrue()
        ->and(class_exists(PasaxPurgeInjectedDataCommand::class))->toBeTrue()
        ->and(class_exists(PasaxScanAccessLogsCommand::class))->toBeTrue()
        ->and(class_exists(PasaxUnblockIpCommand::class))->toBeTrue()
        ->and(class_exists(PasaxUninstallCommand::class))->toBeTrue();

    // Commands legacy aliases
    expect(class_exists(SecurityInstallCommand::class))->toBeTrue()
        ->and(class_exists(SecurityUpgradeCommand::class))->toBeTrue()
        ->and(class_exists(SecurityBaselineCommand::class))->toBeTrue()
        ->and(class_exists(PruneSecurityLogs::class))->toBeTrue()
        ->and(class_exists(PurgeInjectedData::class))->toBeTrue()
        ->and(class_exists(SecurityScanAccessLogs::class))->toBeTrue()
        ->and(class_exists(UnblockIpAddress::class))->toBeTrue();

    // API Controllers
    expect(class_exists(PasaxAllowedIpApiController::class))->toBeTrue()
        ->and(class_exists(PasaxBlockedIpApiController::class))->toBeTrue()
        ->and(class_exists(PasaxIpUnblockRequestApiController::class))->toBeTrue()
        ->and(class_exists(PasaxSecurityLogApiController::class))->toBeTrue()
        ->and(class_exists(PasaxSecuritySettingApiController::class))->toBeTrue()
        ->and(class_exists(PasaxServerSecurityApiController::class))->toBeTrue()
        ->and(class_exists(PasaxUserSessionApiController::class))->toBeTrue();

    // Dashboard Controllers
    expect(class_exists(PasaxAllowedIpController::class))->toBeTrue()
        ->and(class_exists(PasaxBlockedIpController::class))->toBeTrue()
        ->and(class_exists(PasaxLogController::class))->toBeTrue()
        ->and(class_exists(PasaxOverviewController::class))->toBeTrue()
        ->and(class_exists(PasaxServerController::class))->toBeTrue()
        ->and(class_exists(PasaxSessionController::class))->toBeTrue()
        ->and(class_exists(PasaxSettingController::class))->toBeTrue()
        ->and(class_exists(PasaxTicketController::class))->toBeTrue();
});

test('legacy Internal\Pasax classes resolve to Pasax\SecureMonitor classes via compat aliases', function () {
    expect(class_exists('Internal\Pasax\PasaxServiceProvider'))->toBeTrue()
        ->and(class_exists('Internal\Pasax\Facades\Pasax'))->toBeTrue()
        ->and(class_exists('Internal\Pasax\Services\PasaxService'))->toBeTrue()
        ->and(class_exists('Internal\Pasax\Models\PasaxBlockedIp'))->toBeTrue()
        ->and(class_exists('Internal\Pasax\Models\BlockedIp'))->toBeTrue()
        ->and(class_exists('Internal\Pasax\Http\Middleware\PasaxBlockIpAddress'))->toBeTrue();
});
