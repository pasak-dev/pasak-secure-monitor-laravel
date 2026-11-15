<?php

namespace Pasax\SecureMonitor\Services;

/**
 * Legacy alias for PasaxAccessLogScannerService.
 */
class AccessLogScannerService extends PasaxAccessLogScannerService {}

if (! class_exists('Pasax\SecureMonitor\Services\AccessLogScannerService', false)) {
    class_alias(PasaxAccessLogScannerService::class, 'Pasax\SecureMonitor\Services\AccessLogScannerService');
}
