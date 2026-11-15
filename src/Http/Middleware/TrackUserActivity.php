<?php

namespace Pasax\SecureMonitor\Http\Middleware;

/**
 * Legacy alias for PasaxTrackUserActivity.
 */
class TrackUserActivity extends PasaxTrackUserActivity {}

if (! class_exists('Pasax\SecureMonitor\Http\Middleware\TrackUserActivity', false)) {
    class_alias(PasaxTrackUserActivity::class, 'Pasax\SecureMonitor\Http\Middleware\TrackUserActivity');
}
