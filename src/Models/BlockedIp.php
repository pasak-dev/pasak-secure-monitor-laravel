<?php

namespace Pasak\SecureMonitor\Models;

/**
 * @deprecated Use PasakBlockedIp instead.
 *
 * @mixin PasakBlockedIp
 */
if (! class_exists(BlockedIp::class, false)) {
    class_alias(PasakBlockedIp::class, BlockedIp::class);
}
