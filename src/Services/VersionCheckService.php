<?php

namespace Pasax\SecureMonitor\Services;

/**
 * Legacy alias for PasaxVersionCheckService.
 */
class VersionCheckService extends PasaxVersionCheckService {}

if (! class_exists('Pasax\SecureMonitor\Services\VersionCheckService', false)) {
    class_alias(PasaxVersionCheckService::class, 'Pasax\SecureMonitor\Services\VersionCheckService');
}
