<?php

// Backward-compatibility aliases: pasax-dev/secure-monitor was previously
// published under Internal\Pasax and Internal\SecurityMonitor. Old host applications
// referencing those class names keep working via these aliases.

if (!function_exists("pasax_safe_class_alias")) {
    /**
     * Safely define a class/trait/interface alias without crashing if the alias
     * was already declared in host application or an older cached package.
     */
    function pasax_safe_class_alias(string $target, string $alias): void
    {
        if (
            !class_exists($alias, false) &&
            !interface_exists($alias, false) &&
            !trait_exists($alias, false)
        ) {
            try {
                if (
                    class_exists($target) ||
                    interface_exists($target) ||
                    trait_exists($target)
                ) {
                    @class_alias($target, $alias);
                }
            } catch (\Throwable) {
                // Silently ignore if already declared, aliased, or conflicting
            }
        }
    }
}

$serviceProviders = [
    "Internal\SecurityMonitor\SecurityMonitorServiceProvider",
    "Internal\SecurityMonitor\PasaxServiceProvider",
    "Internal\Pasax\PasaxServiceProvider",
];
foreach ($serviceProviders as $alias) {
    pasax_safe_class_alias("Pasax\SecureMonitor\PasaxServiceProvider", $alias);
}

$facades = [
    "Internal\SecurityMonitor\Facades\SecurityMonitor",
    "Internal\SecurityMonitor\Facades\Pasax",
    "Internal\Pasax\Facades\Pasax",
];
foreach ($facades as $alias) {
    pasax_safe_class_alias("Pasax\SecureMonitor\Facades\Pasax", $alias);
}

$services = [
    "Internal\SecurityMonitor\Services\SecurityMonitorService",
    "Internal\SecurityMonitor\Services\PasaxService",
    "Internal\Pasax\Services\PasaxService",
];
foreach ($services as $alias) {
    pasax_safe_class_alias("Pasax\SecureMonitor\Services\PasaxService", $alias);
}

// Backward-compatibility aliases: Pasax* (prefixed class names) <=> legacy non-prefixed class names
$aliases = [
    // Models
    [
        "Pasax\SecureMonitor\Models\PasaxBlockedIp",
        "Pasax\SecureMonitor\Models\BlockedIp",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxLog",
        "Pasax\SecureMonitor\Models\SecurityLog",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxLog",
        "Pasax\SecureMonitor\Models\PasaxSecurityLog",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxLoginAttempt",
        "Pasax\SecureMonitor\Models\LoginAttempt",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxIpUnblockRequest",
        "Pasax\SecureMonitor\Models\IpUnblockRequest",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxUserLogin",
        "Pasax\SecureMonitor\Models\UserLogin",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxTrustedIp",
        "Pasax\SecureMonitor\Models\TrustedIp",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxAllowedIp",
        "Pasax\SecureMonitor\Models\AllowedIp",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxSetting",
        "Pasax\SecureMonitor\Models\SecuritySetting",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxSetting",
        "Pasax\SecureMonitor\Models\PasaxSecuritySetting",
    ],

    // Middleware
    [
        "Pasax\SecureMonitor\Http\Middleware\PasaxBlockIpAddress",
        "Pasax\SecureMonitor\Http\Middleware\BlockIpAddress",
    ],
    [
        "Pasax\SecureMonitor\Http\Middleware\PasaxDetectSecurityThreats",
        "Pasax\SecureMonitor\Http\Middleware\DetectSecurityThreats",
    ],
    [
        "Pasax\SecureMonitor\Http\Middleware\PasaxEnsureSecurityAdmin",
        "Pasax\SecureMonitor\Http\Middleware\EnsureSecurityAdmin",
    ],
    [
        "Pasax\SecureMonitor\Http\Middleware\PasaxInjectAnnouncementBanner",
        "Pasax\SecureMonitor\Http\Middleware\InjectAnnouncementBanner",
    ],
    [
        "Pasax\SecureMonitor\Http\Middleware\PasaxTrackUserActivity",
        "Pasax\SecureMonitor\Http\Middleware\TrackUserActivity",
    ],

    // Listeners
    [
        "Pasax\SecureMonitor\Listeners\PasaxLogFailedLoginAttempt",
        "Pasax\SecureMonitor\Listeners\LogFailedLoginAttempt",
    ],
    [
        "Pasax\SecureMonitor\Listeners\PasaxRecordUserLogin",
        "Pasax\SecureMonitor\Listeners\RecordUserLogin",
    ],
    [
        "Pasax\SecureMonitor\Listeners\PasaxResetLoginAttempts",
        "Pasax\SecureMonitor\Listeners\ResetLoginAttempts",
    ],

    // Rules
    [
        "Pasax\SecureMonitor\Rules\PasaxSafeAssetPath",
        "Pasax\SecureMonitor\Rules\SafeAssetPath",
    ],
    [
        "Pasax\SecureMonitor\Rules\PasaxSafeImageFile",
        "Pasax\SecureMonitor\Rules\SafeImageFile",
    ],

    // Services
    [
        "Pasax\SecureMonitor\Services\PasaxAccessLogScannerService",
        "Pasax\SecureMonitor\Services\AccessLogScannerService",
    ],
    [
        "Pasax\SecureMonitor\Services\PasaxLoginThrottleService",
        "Pasax\SecureMonitor\Services\LoginThrottleService",
    ],
    [
        "Pasax\SecureMonitor\Services\PasaxServerSecurityService",
        "Pasax\SecureMonitor\Services\ServerSecurityService",
    ],
    [
        "Pasax\SecureMonitor\Services\PasaxUserLoginService",
        "Pasax\SecureMonitor\Services\UserLoginService",
    ],
    [
        "Pasax\SecureMonitor\Services\PasaxVersionCheckService",
        "Pasax\SecureMonitor\Services\VersionCheckService",
    ],

    // Support
    [
        "Pasax\SecureMonitor\Support\PasaxDashboardView",
        "Pasax\SecureMonitor\Support\DashboardView",
    ],
    [
        "Pasax\SecureMonitor\Support\PasaxTableResolver",
        "Pasax\SecureMonitor\Support\TableResolver",
    ],

    // Concerns / Traits
    [
        "Pasax\SecureMonitor\Concerns\HasPasaxSecurityRelations",
        "Pasax\SecureMonitor\Concerns\HasSecurityRelations",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\RespondsWithPasaxDashboard",
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\RespondsWithDashboard",
    ],

    // Commands
    [
        "Pasax\SecureMonitor\Console\Commands\PasaxInstallCommand",
        "Pasax\SecureMonitor\Console\Commands\SecurityInstallCommand",
    ],
    [
        "Pasax\SecureMonitor\Console\Commands\PasaxUpgradeCommand",
        "Pasax\SecureMonitor\Console\Commands\SecurityUpgradeCommand",
    ],
    [
        "Pasax\SecureMonitor\Console\Commands\PasaxBaselineCommand",
        "Pasax\SecureMonitor\Console\Commands\SecurityBaselineCommand",
    ],
    [
        "Pasax\SecureMonitor\Console\Commands\PasaxPruneSecurityLogsCommand",
        "Pasax\SecureMonitor\Console\Commands\PruneSecurityLogs",
    ],
    [
        "Pasax\SecureMonitor\Console\Commands\PasaxPurgeInjectedDataCommand",
        "Pasax\SecureMonitor\Console\Commands\PurgeInjectedData",
    ],
    [
        "Pasax\SecureMonitor\Console\Commands\PasaxScanAccessLogsCommand",
        "Pasax\SecureMonitor\Console\Commands\SecurityScanAccessLogs",
    ],
    [
        "Pasax\SecureMonitor\Console\Commands\PasaxUnblockIpCommand",
        "Pasax\SecureMonitor\Console\Commands\UnblockIpAddress",
    ],

    // API Controllers
    [
        "Pasax\SecureMonitor\Http\Controllers\Api\PasaxAllowedIpApiController",
        "Pasax\SecureMonitor\Http\Controllers\Api\AllowedIpApiController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Api\PasaxBlockedIpApiController",
        "Pasax\SecureMonitor\Http\Controllers\Api\BlockedIpApiController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Api\PasaxIpUnblockRequestApiController",
        "Pasax\SecureMonitor\Http\Controllers\Api\IpUnblockRequestApiController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Api\PasaxSecurityLogApiController",
        "Pasax\SecureMonitor\Http\Controllers\Api\SecurityLogApiController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Api\PasaxSecuritySettingApiController",
        "Pasax\SecureMonitor\Http\Controllers\Api\SecuritySettingApiController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Api\PasaxServerSecurityApiController",
        "Pasax\SecureMonitor\Http\Controllers\Api\ServerSecurityApiController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Api\PasaxUserSessionApiController",
        "Pasax\SecureMonitor\Http\Controllers\Api\UserSessionApiController",
    ],

    // Dashboard Controllers
    [
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxAllowedIpController",
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\AllowedIpController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxBlockedIpController",
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\BlockedIpController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxLogController",
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\LogController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxOverviewController",
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\OverviewController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxServerController",
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\ServerController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxSessionController",
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\SessionController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxSettingController",
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\SettingController",
    ],
    [
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxTicketController",
        "Pasax\SecureMonitor\Http\Controllers\Dashboard\TicketController",
    ],

    // Pre-rename namespace (Internal\SecurityMonitor\Models\*)
    [
        "Pasax\SecureMonitor\Models\PasaxBlockedIp",
        "Internal\SecurityMonitor\Models\BlockedIp",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxLog",
        "Internal\SecurityMonitor\Models\SecurityLog",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxLoginAttempt",
        "Internal\SecurityMonitor\Models\LoginAttempt",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxIpUnblockRequest",
        "Internal\SecurityMonitor\Models\IpUnblockRequest",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxUserLogin",
        "Internal\SecurityMonitor\Models\UserLogin",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxTrustedIp",
        "Internal\SecurityMonitor\Models\TrustedIp",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxAllowedIp",
        "Internal\SecurityMonitor\Models\AllowedIp",
    ],
    [
        "Pasax\SecureMonitor\Models\PasaxSetting",
        "Internal\SecurityMonitor\Models\SecuritySetting",
    ],
];

foreach ($aliases as [$target, $alias]) {
    pasax_safe_class_alias($target, $alias);

    // Mirror to Internal\Pasax for backward compatibility
    $internalPasaxTarget = str_replace(
        "Pasax\SecureMonitor\\",
        "Internal\Pasax\\",
        $target,
    );
    pasax_safe_class_alias($target, $internalPasaxTarget);

    $internalPasaxAlias = str_replace(
        "Pasax\SecureMonitor\\",
        "Internal\Pasax\\",
        $alias,
    );
    pasax_safe_class_alias($target, $internalPasaxAlias);
}

// Fallback autoloader for any remaining Internal\Pasax\* references
// Fallback autoloader for any remaining Pasak\* or Internal\Pasak\* references
spl_autoload_register(function ($class) {
    if (str_starts_with($class, "Pasak\\SecureMonitor\\")) {
        $target =
            "Pasax\\SecureMonitor\\" .
            substr($class, strlen("Pasak\\SecureMonitor\\"));
        pasax_safe_class_alias($target, $class);
    }
    if (str_starts_with($class, "Internal\\Pasak\\")) {
        $target =
            "Pasax\\SecureMonitor\\" .
            substr($class, strlen("Internal\\Pasak\\"));
        pasax_safe_class_alias($target, $class);
    }
});
