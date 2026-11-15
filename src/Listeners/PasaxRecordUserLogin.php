<?php

namespace Pasax\SecureMonitor\Listeners;

use Illuminate\Auth\Events\Login;
use Pasax\SecureMonitor\Services\PasaxUserLoginService;
use Throwable;

class PasaxRecordUserLogin
{
    public function __construct(protected PasaxUserLoginService $loginService) {}

    public function handle(Login $event): void
    {
        $user = $event->user;
        $userModel = (string) config(
            'pasax.user_model',
            'App\\Models\\User',
        );

        // Hanya catat login untuk model user yang dikonfigurasi (bila kelasnya ada).
        if (class_exists($userModel) && ! ($user instanceof $userModel)) {
            return;
        }

        try {
            $this->loginService->recordLogin($user, request());
        } catch (Throwable) {
            // Do not block user login if recording fails
        }
    }
}
