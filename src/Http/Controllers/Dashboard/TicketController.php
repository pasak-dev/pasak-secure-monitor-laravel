<?php

namespace Pasak\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasakTicketController.
 */
class TicketController extends PasakTicketController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\TicketController', false)) {
    class_alias(PasakTicketController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\TicketController');
}
