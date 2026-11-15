<?php

namespace Pasax\SecureMonitor\Services;

/**
 * Legacy alias for PasaxServerSecurityService.
 */
class ServerSecurityService extends PasaxServerSecurityService {}

if (! class_exists('Pasax\SecureMonitor\Services\ServerSecurityService', false)) {
    class_alias(PasaxServerSecurityService::class, 'Pasax\SecureMonitor\Services\ServerSecurityService');
}
