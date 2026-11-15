<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

/**
 * Legacy alias for PasaxSettingController.
 */
class SettingController extends PasaxSettingController {}

if (! class_exists('Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\SettingController', false)) {
    class_alias(PasaxSettingController::class, 'Pasax\\SecureMonitor\\Http\\Controllers\\Dashboard\\SettingController');
}
