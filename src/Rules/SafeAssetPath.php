<?php

namespace Pasak\SecureMonitor\Rules;

/**
 * Legacy alias for PasakSafeAssetPath.
 */
class SafeAssetPath extends PasakSafeAssetPath {}

if (! class_exists('Pasak\SecureMonitor\Rules\SafeAssetPath', false)) {
    class_alias(PasakSafeAssetPath::class, 'Pasak\SecureMonitor\Rules\SafeAssetPath');
}
