<?php

namespace Pasax\SecureMonitor\Rules;

/**
 * Legacy alias for PasaxSafeAssetPath.
 */
class SafeAssetPath extends PasaxSafeAssetPath {}

if (! class_exists('Pasax\SecureMonitor\Rules\SafeAssetPath', false)) {
    class_alias(PasaxSafeAssetPath::class, 'Pasax\SecureMonitor\Rules\SafeAssetPath');
}
