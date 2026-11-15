<?php

namespace Pasax\SecureMonitor\Models;

/**
 * @deprecated Use PasaxUserLogin instead.
 *
 * @mixin PasaxUserLogin
 */
if (! class_exists(UserLogin::class, false)) {
    class_alias(PasaxUserLogin::class, UserLogin::class);
}
