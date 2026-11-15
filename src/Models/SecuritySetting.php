<?php

namespace Pasax\SecureMonitor\Models;

/**
 * @deprecated Use PasaxSetting instead.
 *
 * @mixin PasaxSetting
 */
if (! class_exists(SecuritySetting::class, false)) {
    class_alias(PasaxSetting::class, SecuritySetting::class);
}
