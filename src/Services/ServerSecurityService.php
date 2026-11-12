<?php

namespace Pasak\SecureMonitor\Services;

/**
 * Legacy alias for PasakServerSecurityService.
 */
class ServerSecurityService extends PasakServerSecurityService {}

if (! class_exists('Pasak\SecureMonitor\Services\ServerSecurityService', false)) {
    class_alias(PasakServerSecurityService::class, 'Pasak\SecureMonitor\Services\ServerSecurityService');
}
