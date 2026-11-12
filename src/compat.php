<?php

// Backward-compatibility aliases: pasak-dev/secure-monitor was previously
// published under Internal\Pasak and Internal\SecurityMonitor. Old host applications
// referencing those class names keep working via these aliases.

$serviceProviders = [
    'Internal\SecurityMonitor\SecurityMonitorServiceProvider',
    'Internal\SecurityMonitor\PasakServiceProvider',
    'Internal\Pasak\PasakServiceProvider',
];
foreach ($serviceProviders as $alias) {
    if (! class_exists($alias, false) && class_exists('Pasak\SecureMonitor\PasakServiceProvider')) {
        class_alias('Pasak\SecureMonitor\PasakServiceProvider', $alias);
    }
}

$facades = [
    'Internal\SecurityMonitor\Facades\SecurityMonitor',
    'Internal\SecurityMonitor\Facades\Pasak',
    'Internal\Pasak\Facades\Pasak',
];
foreach ($facades as $alias) {
    if (! class_exists($alias, false) && class_exists('Pasak\SecureMonitor\Facades\Pasak')) {
        class_alias('Pasak\SecureMonitor\Facades\Pasak', $alias);
    }
}

$services = [
    'Internal\SecurityMonitor\Services\SecurityMonitorService',
    'Internal\SecurityMonitor\Services\PasakService',
    'Internal\Pasak\Services\PasakService',
];
foreach ($services as $alias) {
    if (! class_exists($alias, false) && class_exists('Pasak\SecureMonitor\Services\PasakService')) {
        class_alias('Pasak\SecureMonitor\Services\PasakService', $alias);
    }
}

// Backward-compatibility aliases: Pasak* (prefixed class names) <=> legacy non-prefixed class names
$aliases = [
    // Models
    ['Pasak\SecureMonitor\Models\PasakBlockedIp', 'Pasak\SecureMonitor\Models\BlockedIp'],
    ['Pasak\SecureMonitor\Models\PasakLog', 'Pasak\SecureMonitor\Models\SecurityLog'],
    ['Pasak\SecureMonitor\Models\PasakLog', 'Pasak\SecureMonitor\Models\PasakSecurityLog'],
    ['Pasak\SecureMonitor\Models\PasakLoginAttempt', 'Pasak\SecureMonitor\Models\LoginAttempt'],
    ['Pasak\SecureMonitor\Models\PasakIpUnblockRequest', 'Pasak\SecureMonitor\Models\IpUnblockRequest'],
    ['Pasak\SecureMonitor\Models\PasakUserLogin', 'Pasak\SecureMonitor\Models\UserLogin'],
    ['Pasak\SecureMonitor\Models\PasakTrustedIp', 'Pasak\SecureMonitor\Models\TrustedIp'],
    ['Pasak\SecureMonitor\Models\PasakAllowedIp', 'Pasak\SecureMonitor\Models\AllowedIp'],
    ['Pasak\SecureMonitor\Models\PasakSetting', 'Pasak\SecureMonitor\Models\SecuritySetting'],
    ['Pasak\SecureMonitor\Models\PasakSetting', 'Pasak\SecureMonitor\Models\PasakSecuritySetting'],

    // Middleware
    ['Pasak\SecureMonitor\Http\Middleware\PasakBlockIpAddress', 'Pasak\SecureMonitor\Http\Middleware\BlockIpAddress'],
    ['Pasak\SecureMonitor\Http\Middleware\PasakDetectSecurityThreats', 'Pasak\SecureMonitor\Http\Middleware\DetectSecurityThreats'],
    ['Pasak\SecureMonitor\Http\Middleware\PasakEnsureSecurityAdmin', 'Pasak\SecureMonitor\Http\Middleware\EnsureSecurityAdmin'],
    ['Pasak\SecureMonitor\Http\Middleware\PasakInjectAnnouncementBanner', 'Pasak\SecureMonitor\Http\Middleware\InjectAnnouncementBanner'],
    ['Pasak\SecureMonitor\Http\Middleware\PasakTrackUserActivity', 'Pasak\SecureMonitor\Http\Middleware\TrackUserActivity'],

    // Listeners
    ['Pasak\SecureMonitor\Listeners\PasakLogFailedLoginAttempt', 'Pasak\SecureMonitor\Listeners\LogFailedLoginAttempt'],
    ['Pasak\SecureMonitor\Listeners\PasakRecordUserLogin', 'Pasak\SecureMonitor\Listeners\RecordUserLogin'],
    ['Pasak\SecureMonitor\Listeners\PasakResetLoginAttempts', 'Pasak\SecureMonitor\Listeners\ResetLoginAttempts'],

    // Rules
    ['Pasak\SecureMonitor\Rules\PasakSafeAssetPath', 'Pasak\SecureMonitor\Rules\SafeAssetPath'],
    ['Pasak\SecureMonitor\Rules\PasakSafeImageFile', 'Pasak\SecureMonitor\Rules\SafeImageFile'],

    // Services
    ['Pasak\SecureMonitor\Services\PasakAccessLogScannerService', 'Pasak\SecureMonitor\Services\AccessLogScannerService'],
    ['Pasak\SecureMonitor\Services\PasakLoginThrottleService', 'Pasak\SecureMonitor\Services\LoginThrottleService'],
    ['Pasak\SecureMonitor\Services\PasakServerSecurityService', 'Pasak\SecureMonitor\Services\ServerSecurityService'],
    ['Pasak\SecureMonitor\Services\PasakUserLoginService', 'Pasak\SecureMonitor\Services\UserLoginService'],
    ['Pasak\SecureMonitor\Services\PasakVersionCheckService', 'Pasak\SecureMonitor\Services\VersionCheckService'],

    // Support
    ['Pasak\SecureMonitor\Support\PasakDashboardView', 'Pasak\SecureMonitor\Support\DashboardView'],
    ['Pasak\SecureMonitor\Support\PasakTableResolver', 'Pasak\SecureMonitor\Support\TableResolver'],

    // Concerns / Traits
    ['Pasak\SecureMonitor\Concerns\HasPasakSecurityRelations', 'Pasak\SecureMonitor\Concerns\HasSecurityRelations'],
    ['Pasak\SecureMonitor\Http\Controllers\Dashboard\RespondsWithPasakDashboard', 'Pasak\SecureMonitor\Http\Controllers\Dashboard\RespondsWithDashboard'],

    // Commands
    ['Pasak\SecureMonitor\Console\Commands\PasakInstallCommand', 'Pasak\SecureMonitor\Console\Commands\SecurityInstallCommand'],
    ['Pasak\SecureMonitor\Console\Commands\PasakUpgradeCommand', 'Pasak\SecureMonitor\Console\Commands\SecurityUpgradeCommand'],
    ['Pasak\SecureMonitor\Console\Commands\PasakBaselineCommand', 'Pasak\SecureMonitor\Console\Commands\SecurityBaselineCommand'],
    ['Pasak\SecureMonitor\Console\Commands\PasakPruneSecurityLogsCommand', 'Pasak\SecureMonitor\Console\Commands\PruneSecurityLogs'],
    ['Pasak\SecureMonitor\Console\Commands\PasakPurgeInjectedDataCommand', 'Pasak\SecureMonitor\Console\Commands\PurgeInjectedData'],
    ['Pasak\SecureMonitor\Console\Commands\PasakScanAccessLogsCommand', 'Pasak\SecureMonitor\Console\Commands\SecurityScanAccessLogs'],
    ['Pasak\SecureMonitor\Console\Commands\PasakUnblockIpCommand', 'Pasak\SecureMonitor\Console\Commands\UnblockIpAddress'],

    // API Controllers
    ['Pasak\SecureMonitor\Http\Controllers\Api\PasakAllowedIpApiController', 'Pasak\SecureMonitor\Http\Controllers\Api\AllowedIpApiController'],
    ['Pasak\SecureMonitor\Http\Controllers\Api\PasakBlockedIpApiController', 'Pasak\SecureMonitor\Http\Controllers\Api\BlockedIpApiController'],
    ['Pasak\SecureMonitor\Http\Controllers\Api\PasakIpUnblockRequestApiController', 'Pasak\SecureMonitor\Http\Controllers\Api\IpUnblockRequestApiController'],
    ['Pasak\SecureMonitor\Http\Controllers\Api\PasakSecurityLogApiController', 'Pasak\SecureMonitor\Http\Controllers\Api\SecurityLogApiController'],
    ['Pasak\SecureMonitor\Http\Controllers\Api\PasakSecuritySettingApiController', 'Pasak\SecureMonitor\Http\Controllers\Api\SecuritySettingApiController'],
    ['Pasak\SecureMonitor\Http\Controllers\Api\PasakServerSecurityApiController', 'Pasak\SecureMonitor\Http\Controllers\Api\ServerSecurityApiController'],
    ['Pasak\SecureMonitor\Http\Controllers\Api\PasakUserSessionApiController', 'Pasak\SecureMonitor\Http\Controllers\Api\UserSessionApiController'],

    // Dashboard Controllers
    ['Pasak\SecureMonitor\Http\Controllers\Dashboard\PasakAllowedIpController', 'Pasak\SecureMonitor\Http\Controllers\Dashboard\AllowedIpController'],
    ['Pasak\SecureMonitor\Http\Controllers\Dashboard\PasakBlockedIpController', 'Pasak\SecureMonitor\Http\Controllers\Dashboard\BlockedIpController'],
    ['Pasak\SecureMonitor\Http\Controllers\Dashboard\PasakLogController', 'Pasak\SecureMonitor\Http\Controllers\Dashboard\LogController'],
    ['Pasak\SecureMonitor\Http\Controllers\Dashboard\PasakOverviewController', 'Pasak\SecureMonitor\Http\Controllers\Dashboard\OverviewController'],
    ['Pasak\SecureMonitor\Http\Controllers\Dashboard\PasakServerController', 'Pasak\SecureMonitor\Http\Controllers\Dashboard\ServerController'],
    ['Pasak\SecureMonitor\Http\Controllers\Dashboard\PasakSessionController', 'Pasak\SecureMonitor\Http\Controllers\Dashboard\SessionController'],
    ['Pasak\SecureMonitor\Http\Controllers\Dashboard\PasakSettingController', 'Pasak\SecureMonitor\Http\Controllers\Dashboard\SettingController'],
    ['Pasak\SecureMonitor\Http\Controllers\Dashboard\PasakTicketController', 'Pasak\SecureMonitor\Http\Controllers\Dashboard\TicketController'],

    // Pre-rename namespace (Internal\SecurityMonitor\Models\*)
    ['Pasak\SecureMonitor\Models\PasakBlockedIp', 'Internal\SecurityMonitor\Models\BlockedIp'],
    ['Pasak\SecureMonitor\Models\PasakLog', 'Internal\SecurityMonitor\Models\SecurityLog'],
    ['Pasak\SecureMonitor\Models\PasakLoginAttempt', 'Internal\SecurityMonitor\Models\LoginAttempt'],
    ['Pasak\SecureMonitor\Models\PasakIpUnblockRequest', 'Internal\SecurityMonitor\Models\IpUnblockRequest'],
    ['Pasak\SecureMonitor\Models\PasakUserLogin', 'Internal\SecurityMonitor\Models\UserLogin'],
    ['Pasak\SecureMonitor\Models\PasakTrustedIp', 'Internal\SecurityMonitor\Models\TrustedIp'],
    ['Pasak\SecureMonitor\Models\PasakAllowedIp', 'Internal\SecurityMonitor\Models\AllowedIp'],
    ['Pasak\SecureMonitor\Models\PasakSetting', 'Internal\SecurityMonitor\Models\SecuritySetting'],
];

foreach ($aliases as [$target, $alias]) {
    if (! class_exists($alias, false) && ! trait_exists($alias, false) && (class_exists($target) || trait_exists($target))) {
        class_alias($target, $alias);
    }

    // Mirror to Internal\Pasak for backward compatibility
    $internalPasakTarget = str_replace('Pasak\SecureMonitor\\', 'Internal\Pasak\\', $target);
    if (! class_exists($internalPasakTarget, false) && ! trait_exists($internalPasakTarget, false) && (class_exists($target) || trait_exists($target))) {
        class_alias($target, $internalPasakTarget);
    }

    $internalPasakAlias = str_replace('Pasak\SecureMonitor\\', 'Internal\Pasak\\', $alias);
    if (! class_exists($internalPasakAlias, false) && ! trait_exists($internalPasakAlias, false) && (class_exists($target) || trait_exists($target))) {
        class_alias($target, $internalPasakAlias);
    }
}

// Fallback autoloader for any remaining Internal\Pasak\* references
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'Internal\Pasak\\')) {
        $target = 'Pasak\SecureMonitor\\'.substr($class, strlen('Internal\Pasak\\'));
        if (class_exists($target) || interface_exists($target) || trait_exists($target)) {
            class_alias($target, $class);
        }
    }
});
