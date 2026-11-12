<?php

namespace Pasak\SecureMonitor\Listeners;

/**
 * Legacy alias for PasakRecordUserLogin.
 */
class RecordUserLogin extends PasakRecordUserLogin {}

if (! class_exists('Pasak\SecureMonitor\Listeners\RecordUserLogin', false)) {
    class_alias(PasakRecordUserLogin::class, 'Pasak\SecureMonitor\Listeners\RecordUserLogin');
}
