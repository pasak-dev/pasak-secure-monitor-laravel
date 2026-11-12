<?php

namespace Pasak\SecureMonitor\Rules;

/**
 * Legacy alias for PasakSafeImageFile.
 */
class SafeImageFile extends PasakSafeImageFile {}

if (! class_exists('Pasak\SecureMonitor\Rules\SafeImageFile', false)) {
    class_alias(PasakSafeImageFile::class, 'Pasak\SecureMonitor\Rules\SafeImageFile');
}
