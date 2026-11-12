<?php

namespace Pasak\SecureMonitor\Support;

/**
 * Legacy alias for PasakTableResolver.
 */
class TableResolver extends PasakTableResolver {}

if (! class_exists('Pasak\SecureMonitor\Support\TableResolver', false)) {
    class_alias(PasakTableResolver::class, 'Pasak\SecureMonitor\Support\TableResolver');
}
