<?php

namespace Pasak\SecureMonitor\Http\Middleware;

/**
 * Legacy alias for PasakInjectAnnouncementBanner.
 */
class InjectAnnouncementBanner extends PasakInjectAnnouncementBanner {}

if (! class_exists('Pasak\SecureMonitor\Http\Middleware\InjectAnnouncementBanner', false)) {
    class_alias(PasakInjectAnnouncementBanner::class, 'Pasak\SecureMonitor\Http\Middleware\InjectAnnouncementBanner');
}
