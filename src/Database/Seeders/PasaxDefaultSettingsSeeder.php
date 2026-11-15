<?php

namespace Pasax\SecureMonitor\Database\Seeders;

use Illuminate\Database\Seeder;
use Pasax\SecureMonitor\Services\PasaxService;

/**
 * Seeds the built-in default security settings into the pasax_settings
 * table so every tunable knob lives in the database and can be adjusted
 * from the Pasax dashboard without editing .env.
 *
 * Idempotent: existing setting rows are never overwritten unless $overwrite is true.
 *
 * Manual usage:  php artisan db:seed --class=Pasax\SecureMonitor\Database\Seeders\PasaxDefaultSettingsSeeder --force
 */
class PasaxDefaultSettingsSeeder extends Seeder
{
    public function __construct(protected PasaxService $security) {}

    /**
     * Run the seeding.
     *
     * @param  bool  $overwrite  Whether to overwrite existing settings with defaults
     * @return array<int, string> newly created or updated setting keys
     */
    public function run(bool $overwrite = false): array
    {
        return $this->security->seedDefaultSettings($overwrite);
    }
}
