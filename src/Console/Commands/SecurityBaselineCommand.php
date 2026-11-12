<?php

namespace Pasak\SecureMonitor\Console\Commands;

/**
 * Legacy alias for PasakBaselineCommand.
 */
class SecurityBaselineCommand extends PasakBaselineCommand {}

if (! class_exists('Pasak\\SecureMonitor\\Console\\Commands\\SecurityBaselineCommand', false)) {
    class_alias(PasakBaselineCommand::class, 'Pasak\\SecureMonitor\\Console\\Commands\\SecurityBaselineCommand');
}
