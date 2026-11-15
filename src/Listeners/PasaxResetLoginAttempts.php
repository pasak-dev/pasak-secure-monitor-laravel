<?php

namespace Pasax\SecureMonitor\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Gate;
use Pasax\SecureMonitor\Services\PasaxLoginThrottleService;
use Pasax\SecureMonitor\Services\PasaxService;

/**
 * Mengosongkan riwayat kegagalan login setelah pengguna berhasil masuk,
 * sehingga cooldown bertingkat tidak "menempel" pada pengguna yang sah,
 * serta otomatis membuka blokir IP apabila yang login adalah admin.
 */
class PasaxResetLoginAttempts
{
    public function __construct(
        protected PasaxLoginThrottleService $throttle,
        protected PasaxService $security,
    ) {}

    public function handle(Login $event): void
    {
        $ip = request()->ip();
        $this->throttle->registerSuccess($event->user?->email, $ip);

        // Buka blokir IP otomatis bila yang login adalah administrator.
        if (
            $ip &&
            $event->user &&
            Gate::forUser($event->user)->allows('manage-pasax')
        ) {
            $this->security->unblockIp($ip);
        }
    }
}
