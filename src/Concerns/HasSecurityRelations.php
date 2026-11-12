<?php

namespace Pasak\SecureMonitor\Concerns;

// Alias nama legasi: HasSecurityRelations diarahkan ke tur utama
// HasPasakSecurityRelations (bila compat.php belum sempat memasangnya).
if (! trait_exists('Pasak\SecureMonitor\Concerns\HasSecurityRelations', false)) {
    class_alias(HasPasakSecurityRelations::class, 'Pasak\SecureMonitor\Concerns\HasSecurityRelations');
}
