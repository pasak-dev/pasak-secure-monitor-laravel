<?php

namespace Pasax\SecureMonitor\Models;

/**
 * @deprecated Use PasaxBlockedIp instead.
 *
 * @mixin PasaxBlockedIp
 */
if (! class_exists(BlockedIp::class, false)) {
    class_alias(PasaxBlockedIp::class, BlockedIp::class);
}
