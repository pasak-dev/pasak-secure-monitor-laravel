<?php

namespace Pasax\SecureMonitor\Models;

/**
 * @deprecated Use PasaxAllowedIp instead.
 *
 * @mixin PasaxAllowedIp
 */
if (! class_exists(AllowedIp::class, false)) {
    class_alias(PasaxAllowedIp::class, AllowedIp::class);
}
