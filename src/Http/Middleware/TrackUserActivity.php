<?php

namespace Pasak\SecureMonitor\Http\Middleware;

/**
 * Legacy alias for PasakTrackUserActivity.
 */
class TrackUserActivity extends PasakTrackUserActivity {}

if (! class_exists('Pasak\SecureMonitor\Http\Middleware\TrackUserActivity', false)) {
    class_alias(PasakTrackUserActivity::class, 'Pasak\SecureMonitor\Http\Middleware\TrackUserActivity');
}
