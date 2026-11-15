<?php

namespace Pasax\SecureMonitor\Models;

/**
 * @deprecated Use PasaxTrustedIp instead.
 *
 * @mixin PasaxTrustedIp
 */
if (! class_exists(TrustedIp::class, false)) {
    class_alias(PasaxTrustedIp::class, TrustedIp::class);
}
