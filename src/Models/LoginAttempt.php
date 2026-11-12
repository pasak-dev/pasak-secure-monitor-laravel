<?php

namespace Pasak\SecureMonitor\Models;

/**
 * @deprecated Use PasakLoginAttempt instead.
 *
 * @mixin PasakLoginAttempt
 */
if (! class_exists(LoginAttempt::class, false)) {
    class_alias(PasakLoginAttempt::class, LoginAttempt::class);
}
