<?php

namespace Pasak\SecureMonitor\Services;

/**
 * Legacy alias for PasakAccessLogScannerService.
 */
class AccessLogScannerService extends PasakAccessLogScannerService {}

if (! class_exists('Pasak\SecureMonitor\Services\AccessLogScannerService', false)) {
    class_alias(PasakAccessLogScannerService::class, 'Pasak\SecureMonitor\Services\AccessLogScannerService');
}
