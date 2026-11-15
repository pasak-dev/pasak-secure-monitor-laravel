<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

// Alias nama legasi: RespondsWithDashboard diarahkan ke tur utama
// RespondsWithPasaxDashboard (bila compat.php belum sempat memasangnya).
if (! trait_exists('Pasax\SecureMonitor\Http\Controllers\Dashboard\RespondsWithDashboard', false)) {
    class_alias(RespondsWithPasaxDashboard::class, 'Pasax\SecureMonitor\Http\Controllers\Dashboard\RespondsWithDashboard');
}
