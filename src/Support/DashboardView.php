<?php

namespace Pasax\SecureMonitor\Support;

/**
 * Legacy alias for PasaxDashboardView.
 */
class DashboardView extends PasaxDashboardView {}

if (! class_exists('Pasax\SecureMonitor\Support\DashboardView', false)) {
    class_alias(PasaxDashboardView::class, 'Pasax\SecureMonitor\Support\DashboardView');
}
