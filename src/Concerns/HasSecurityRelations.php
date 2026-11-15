<?php

namespace Pasax\SecureMonitor\Concerns;

// Alias nama legasi: HasSecurityRelations diarahkan ke tur utama
// HasPasaxSecurityRelations (bila compat.php belum sempat memasangnya).
if (! trait_exists('Pasax\SecureMonitor\Concerns\HasSecurityRelations', false)) {
    class_alias(HasPasaxSecurityRelations::class, 'Pasax\SecureMonitor\Concerns\HasSecurityRelations');
}
