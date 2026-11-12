<?php

namespace Pasak\SecureMonitor\Support;

/**
 * Legacy alias for PasakDashboardView.
 */
class DashboardView extends PasakDashboardView {}

if (! class_exists('Pasak\SecureMonitor\Support\DashboardView', false)) {
    class_alias(PasakDashboardView::class, 'Pasak\SecureMonitor\Support\DashboardView');
}
