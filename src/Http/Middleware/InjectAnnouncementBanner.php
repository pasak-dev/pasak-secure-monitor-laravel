<?php

namespace Pasax\SecureMonitor\Http\Middleware;

/**
 * Legacy alias for PasaxInjectAnnouncementBanner.
 */
class InjectAnnouncementBanner extends PasaxInjectAnnouncementBanner {}

if (! class_exists('Pasax\SecureMonitor\Http\Middleware\InjectAnnouncementBanner', false)) {
    class_alias(PasaxInjectAnnouncementBanner::class, 'Pasax\SecureMonitor\Http\Middleware\InjectAnnouncementBanner');
}
