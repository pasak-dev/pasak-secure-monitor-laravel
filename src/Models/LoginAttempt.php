<?php

namespace Pasax\SecureMonitor\Models;

/**
 * @deprecated Use PasaxLoginAttempt instead.
 *
 * @mixin PasaxLoginAttempt
 */
if (! class_exists(LoginAttempt::class, false)) {
    class_alias(PasaxLoginAttempt::class, LoginAttempt::class);
}
