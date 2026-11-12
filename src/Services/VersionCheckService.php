<?php

namespace Pasak\SecureMonitor\Services;

/**
 * Legacy alias for PasakVersionCheckService.
 */
class VersionCheckService extends PasakVersionCheckService {}

if (! class_exists('Pasak\SecureMonitor\Services\VersionCheckService', false)) {
    class_alias(PasakVersionCheckService::class, 'Pasak\SecureMonitor\Services\VersionCheckService');
}
