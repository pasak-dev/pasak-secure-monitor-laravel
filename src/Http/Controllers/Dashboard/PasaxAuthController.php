<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Pasax\SecureMonitor\Models\PasaxSetting;
use Pasax\SecureMonitor\Services\PasaxLoginThrottleService;
use Pasax\SecureMonitor\Services\PasaxUserLoginService;
use Pasax\SecureMonitor\Support\PasaxDashboardSecret;

class PasaxAuthController
{
    /**
     * Tampilkan formulir login mandiri dashboard Pasax.
     */
    public function showLoginForm(Request $request)
    {
        // Jika sudah login, langsung alihkan ke overview atau URL tujuan
        if ($request->hasSession() && $request->session()->get('pasax_dashboard_authenticated') === true) {
            $prefix = trim((string) config('pasax.dashboard.prefix', 'pasax'), '/');

            return redirect()->to(url($prefix));
        }

        $throttle = app(PasaxLoginThrottleService::class);
        $lockedAttempt = $throttle->enabled() ? $throttle->activeLockoutForIp($request->ip()) : null;

        $secret = $this->resolveExpectedSecret();
        $secretConfigured = $secret !== '';

        /** @var view-string $viewName */
        $viewName = 'pages.pasax.login';

        return view($viewName, [
            'secretConfigured' => $secretConfigured,
            'defaultUsername' => $this->resolveExpectedUsername(),
            'defaultPassword' => $this->resolveDefaultPassword(),
            'isPasswordStored' => PasaxDashboardSecret::isPasswordStoredInDatabase(),
            'lockout' => $lockedAttempt ? [
                'seconds' => $lockedAttempt->secondsRemaining(),
                'level' => $lockedAttempt->lockout_level,
                'message' => $throttle->lockoutMessage($lockedAttempt->secondsRemaining()),
            ] : null,
        ]);
    }

    /**
     * Proses pengiriman kredensial login (username & secret password).
     */
    public function login(Request $request)
    {
        $throttle = app(PasaxLoginThrottleService::class);
        $username = (string) $request->input('username');
        $ip = (string) $request->ip();

        if ($throttle->enabled()) {
            $status = $throttle->status($username, $ip);
            if ($status['locked']) {
                $msg = $status['message'] ?? $throttle->lockoutMessage($status['seconds']);

                return back()
                    ->withInput($request->only('username'))
                    ->withErrors([
                        'pasax_login' => $msg,
                        'username' => $msg,
                    ]);
            }
        }

        $throttleKey = 'pasax_login_throttle:'.Str::lower($request->ip());

        // Perlindungan brute-force: maksimal 5 percobaan gagal per 60 detik
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $msg = __('Terlalu banyak percobaan login gagal. Silakan coba lagi dalam :seconds detik.', ['seconds' => $seconds]);

            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'pasax_login' => $msg,
                    'username' => $msg,
                ]);
        }

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $expectedUser = $this->resolveExpectedUsername();
        $expectedSecret = $this->resolveExpectedSecret();
        $defaultPassword = $this->resolveDefaultPassword();

        // Jika secret belum terisi di .env
        if ($expectedSecret === '') {
            $msg = __('PASAX_DASHBOARD_SECRET belum dikonfigurasi pada .env. Jalankan "php artisan pasax:secret" terlebih dahulu.');

            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'pasax_login' => $msg,
                    'password' => $msg,
                ]);
        }

        $inputUser = (string) $request->input('username');
        $inputPassword = (string) $request->input('password');

        $userValid = hash_equals($expectedUser, $inputUser);
        $passResult = PasaxDashboardSecret::verifyPassword($inputPassword, $expectedSecret, $defaultPassword);

        if (! $userValid || ! $passResult['valid']) {
            RateLimiter::hit($throttleKey, 60);

            if ($throttle->enabled()) {
                $failStatus = $throttle->registerFailure($username, $ip);
                if ($failStatus['locked']) {
                    $msg = $failStatus['message'] ?? $throttle->lockoutMessage($failStatus['seconds']);

                    return back()
                        ->withInput($request->only('username'))
                        ->withErrors([
                            'pasax_login' => $msg,
                            'username' => $msg,
                            'password' => $msg,
                        ]);
                }
            }
            $msg = __('Username atau password salah. Periksa kredensial pada berkas .env Anda.');

            return back()
                ->withInput($request->only('username'))
                ->withErrors([
                    'pasax_login' => $msg,
                    'password' => $msg,
                ]);
        }

        // Login berhasil, bersihkan riwayat throttle
        RateLimiter::clear($throttleKey);
        if ($throttle->enabled()) {
            $throttle->registerSuccess($inputUser, $ip);
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
            $request->session()->put('pasax_dashboard_authenticated', true);
            $request->session()->put('pasax_dashboard_user', $inputUser);
            $request->session()->put('pasax_dashboard_login_at', now()->timestamp);

            // Catat sesi login khusus dasbor Pasax
            $user = $this->resolveUserForDashboardLogin($inputUser);
            if ($user instanceof Authenticatable) {
                try {
                    app(PasaxUserLoginService::class)->recordDashboardLogin($user, $request);
                } catch (\Throwable) {
                }
            }

            // Jika login menggunakan secret darurat atau password default, minta ganti password
            if (! empty($passResult['is_emergency']) || ! empty($passResult['is_default'])) {
                $request->session()->put('pasax_dashboard_needs_password_change', true);
                if (! empty($passResult['is_emergency'])) {
                    $request->session()->put('pasax_dashboard_is_emergency_login', true);
                } else {
                    $request->session()->forget('pasax_dashboard_is_emergency_login');
                }
            } else {
                $request->session()->forget(['pasax_dashboard_needs_password_change', 'pasax_dashboard_is_emergency_login']);
                $request->session()->forget('pasax_dashboard_is_emergency_login');
            }
        }

        $intended = $request->session()->pull('pasax_dashboard_intended');
        $prefix = trim((string) config('pasax.dashboard.prefix', 'pasax'), '/');

        // Pastikan URL tujuan memang mengarah ke area dashboard Pasax
        if (! empty($intended) && (str_contains($intended, '/'.$prefix) || str_contains($intended, $prefix.'-'))) {
            return redirect()->to($intended);
        }

        return redirect()->to(url($prefix));
    }

    /**
     * Resolusi pengguna untuk mencatat login dasbor Pasax.
     */
    protected function resolveUserForDashboardLogin(string $username): ?Authenticatable
    {
        $userModel = (string) config('pasax.user_model', 'App\\Models\\User');
        if (class_exists($userModel)) {
            $user = $userModel::query()
                ->where('email', $username)
                ->orWhere('name', $username)
                ->first();

            return $user ?? $userModel::query()->first();
        }

        return null;
    }

    /**
     * Perbarui password dasbor dan simpan hash HMAC dengan PASAX_DASHBOARD_SECRET ke database.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $secret = $this->resolveExpectedSecret();
        if ($secret === '') {
            return back()->with('toast', [
                'type' => 'error',
                'message' => __('PASAX_DASHBOARD_SECRET belum dikonfigurasi pada .env.'),
            ]);
        }

        $newPassword = (string) $request->input('password');

        // Simpan hash password ke database
        PasaxDashboardSecret::storePasswordInDatabase($newPassword, $secret);

        // Bersihkan tanda kebutuhan ganti password
        if ($request->hasSession()) {
            $request->session()->forget(['pasax_dashboard_needs_password_change', 'pasax_dashboard_is_emergency_login']);
        }

        $successMsg = __('Password dasbor berhasil diperbarui dan disimpan aman di database.');

        return back()->with('toast', [
            'type' => 'success',
            'message' => $successMsg,
        ])->with('status', $successMsg);
    }

    /**
     * Logout dari sesi dashboard Pasax.
     */
    public function logout(Request $request)
    {
        if ($request->hasSession()) {
            $request->session()->forget([
                'pasax_dashboard_authenticated',
                'pasax_dashboard_user',
                'pasax_dashboard_login_at',
                'pasax_dashboard_intended',
                'pasax_dashboard_needs_password_change',
            ]);
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('pasax.dashboard.login')->with('status', __('Sesi dashboard Pasax telah diakhiri.'));
    }

    /**
     * Dapatkan secret password yang diharapkan dari config atau berkas .env.
     */
    protected function resolveExpectedSecret(): string
    {
        $secret = (string) config('pasax.dashboard.secret');
        if ($secret !== '') {
            return $secret;
        }

        return (string) (PasaxDashboardSecret::readFromEnvFile(base_path('.env')) ?? '');
    }

    /**
     * Dapatkan username yang diharapkan dari database, config, atau berkas .env.
     */
    protected function resolveExpectedUsername(): string
    {
        try {
            $dbUser = PasaxSetting::query()
                ->where('key', PasaxDashboardSecret::DB_USERNAME_KEY)
                ->value('value');
            if (! empty($dbUser)) {
                return (string) $dbUser;
            }
        } catch (\Throwable) {
        }

        $username = (string) config('pasax.dashboard.auth.username');
        if ($username !== '') {
            return $username;
        }

        return (string) (PasaxDashboardSecret::readUsernameFromEnvFile(base_path('.env')) ?? PasaxDashboardSecret::DEFAULT_USERNAME);
    }

    /**
     * Dapatkan default password awal dari config atau berkas .env.
     */
    protected function resolveDefaultPassword(): string
    {
        $password = (string) config('pasax.dashboard.auth.default_password');
        if ($password !== '') {
            return $password;
        }

        return (string) (PasaxDashboardSecret::readPasswordFromEnvFile(base_path('.env')) ?? PasaxDashboardSecret::DEFAULT_PASSWORD);
    }
}
