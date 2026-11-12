<?php

namespace Pasak\SecureMonitor\Models;

/**
 * @deprecated Use PasakIpUnblockRequest instead.
 *
 * @mixin PasakIpUnblockRequest
 */
if (! class_exists(IpUnblockRequest::class, false)) {
    class_alias(PasakIpUnblockRequest::class, IpUnblockRequest::class);
}
