<?php

namespace Pasak\SecureMonitor\Models;

/**
 * @deprecated Use PasakUserLogin instead.
 *
 * @mixin PasakUserLogin
 */
if (! class_exists(UserLogin::class, false)) {
    class_alias(PasakUserLogin::class, UserLogin::class);
}
