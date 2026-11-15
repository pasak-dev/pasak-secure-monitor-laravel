<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasaxTicketController.
 */
class TicketController extends PasaxTicketController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\TicketController', false)) {
    class_alias(PasaxTicketController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\TicketController');
}
