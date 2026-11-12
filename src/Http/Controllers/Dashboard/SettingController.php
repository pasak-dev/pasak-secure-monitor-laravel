<?php

namespace Pasak\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasakSettingController.
 */
class SettingController extends PasakSettingController {}

if (! class_exists('Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\SettingController', false)) {
    class_alias(PasakSettingController::class, 'Pasak\\SecureMonitor\\Http\\Controllers\\Dashboard\\SettingController');
}
