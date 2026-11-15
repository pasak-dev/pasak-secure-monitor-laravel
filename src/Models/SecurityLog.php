<?php

namespace Pasax\SecureMonitor\Models;

/**
 * @deprecated Use PasaxLog instead.
 *
 * @mixin PasaxLog
 */
if (! class_exists(SecurityLog::class, false)) {
    class_alias(PasaxLog::class, SecurityLog::class);
}
