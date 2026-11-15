<?php

namespace Pasax\SecureMonitor\Listeners;

/**
 * Legacy alias for PasaxRecordUserLogin.
 */
class RecordUserLogin extends PasaxRecordUserLogin {}

if (! class_exists('Pasax\SecureMonitor\Listeners\RecordUserLogin', false)) {
    class_alias(PasaxRecordUserLogin::class, 'Pasax\SecureMonitor\Listeners\RecordUserLogin');
}
