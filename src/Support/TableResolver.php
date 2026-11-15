<?php

namespace Pasax\SecureMonitor\Support;

/**
 * Legacy alias for PasaxTableResolver.
 */
class TableResolver extends PasaxTableResolver {}

if (! class_exists('Pasax\SecureMonitor\Support\TableResolver', false)) {
    class_alias(PasaxTableResolver::class, 'Pasax\SecureMonitor\Support\TableResolver');
}
