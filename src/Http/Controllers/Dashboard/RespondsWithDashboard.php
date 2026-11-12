<?php

namespace Pasak\SecureMonitor\Http\Controllers\Dashboard;

// Alias nama legasi: RespondsWithDashboard diarahkan ke tur utama
// RespondsWithPasakDashboard (bila compat.php belum sempat memasangnya).
if (! trait_exists('Pasak\SecureMonitor\Http\Controllers\Dashboard\RespondsWithDashboard', false)) {
    class_alias(RespondsWithPasakDashboard::class, 'Pasak\SecureMonitor\Http\Controllers\Dashboard\RespondsWithDashboard');
}
