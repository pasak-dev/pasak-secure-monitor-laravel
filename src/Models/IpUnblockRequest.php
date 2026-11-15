<?php

namespace Pasax\SecureMonitor\Models;

/**
 * @deprecated Use PasaxIpUnblockRequest instead.
 *
 * @mixin PasaxIpUnblockRequest
 */
if (! class_exists(IpUnblockRequest::class, false)) {
    class_alias(PasaxIpUnblockRequest::class, IpUnblockRequest::class);
}
