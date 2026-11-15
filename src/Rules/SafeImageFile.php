<?php

namespace Pasax\SecureMonitor\Rules;

/**
 * Legacy alias for PasaxSafeImageFile.
 */
class SafeImageFile extends PasaxSafeImageFile {}

if (! class_exists('Pasax\SecureMonitor\Rules\SafeImageFile', false)) {
    class_alias(PasaxSafeImageFile::class, 'Pasax\SecureMonitor\Rules\SafeImageFile');
}
