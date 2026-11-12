<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Pasak\SecureMonitor\Models\PasakSetting;
use Pasak\SecureMonitor\Support\PasakDashboardSecret;

beforeEach(function () {
    config()->set('pasak.dashboard.enabled', true);
    config()->set('pasak.dashboard.auth.enabled', true);
    config()->set('pasak.dashboard.auth.username', 'admin');
    config()->set('pasak.dashboard.auth.default_password', 'Admin');
    config()->set('pasak.dashboard.rotating', false);

    // Salin stub views ke resource_path
    $viewsTarget = resource_path('views/pages/pasak');
    File::ensureDirectoryExists($viewsTarget);
    File::copyDirectory(
        dirname(__DIR__, 2).'/stubs/blade/pages/pasak',
        $viewsTarget,
    );

    // Pastikan update-password-modal ada di views
    if (! File::exists($viewsTarget.'/update-password-modal.blade.php')) {
        File::copy(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak/update-password-modal.blade.php',
            $viewsTarget.'/update-password-modal.blade.php',
        );
    }

    // Muat rute dashboard blade
    require __DIR__.'/../../routes/pasak-dashboard-blade.php';
    Route::getRoutes()->refreshNameLookups();

    // Buat .env untuk testing dengan kredensial pasak
    $envPath = base_path('.env');
    $secret = 'test-secret-key-1234567890abcdefghijklmnopqrstuvwxyz';
    File::put(
        $envPath,
        "PASAK_DASHBOARD_USERNAME=admin\nPASAK_DASHBOARD_PASSWORD=Admin\nPASAK_DASHBOARD_SECRET={$secret}\n",
    );

    // Bersihkan setting hash lama di database
    PasakSetting::query()->where('key', PasakDashboardSecret::DB_PASSWORD_HASH_KEY)->delete();
});

afterEach(function () {
    $envPath = base_path('.env');
    if (File::exists($envPath)) {
        File::delete($envPath);
    }
});

it('redirects unauthenticated guest accessing dashboard to pasak login', function () {
    $response = $this->get('/pasak');

    $response->assertRedirect(route('pasak.dashboard.login'));
});

it('renders the pasak login form with username and password fields', function () {
    $response = $this->get(route('pasak.dashboard.login'));

    $response->assertOk();
    $response->assertSee('name="username"', false);
    $response->assertSee('name="password"', false);
    $response->assertSee('sec-header-logo-light', false);
    $response->assertSee('sec-header-logo-dark', false);
    $response->assertSee('PASAK_DASHBOARD_SECRET');
});

it('rejects invalid login credentials and stays unauthenticated', function () {
    $response = $this->post(route('pasak.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'wrong-secret-key',
    ]);

    $response->assertSessionHasErrors('password');
    expect(session('pasak_dashboard_authenticated'))->toBeNull();
});

it('authenticates user with default password Admin and flags session for password change', function () {
    $response = $this->post(route('pasak.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'Admin',
    ]);

    $response->assertRedirect('/pasak');
    expect(session('pasak_dashboard_authenticated'))->toBeTrue()
        ->and(session('pasak_dashboard_needs_password_change'))->toBeTrue();

    // Akses dasbor menampilkan peringatan ganti password
    $dashResponse = $this->withSession([
        'pasak_dashboard_authenticated' => true,
        'pasak_dashboard_needs_password_change' => true,
    ])->get('/pasak');

    $dashResponse->assertOk();
    expect($dashResponse->getContent())->toContain('pasakUpdatePasswordModal');
    $dashResponse->assertSee('pasakUpdatePasswordModal');
});

it('authenticates user with secret key directly as fallback', function () {
    $response = $this->post(route('pasak.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'test-secret-key-1234567890abcdefghijklmnopqrstuvwxyz',
    ]);

    $response->assertRedirect('/pasak');
    expect(session('pasak_dashboard_authenticated'))->toBeTrue();
});

it('allows updating dashboard password and stores HMAC hash in database', function () {
    $secret = 'test-secret-key-1234567890abcdefghijklmnopqrstuvwxyz';

    // Kirim request update password
    $response = $this->withSession([
        'pasak_dashboard_authenticated' => true,
        'pasak_dashboard_needs_password_change' => true,
    ])->post(route('pasak.dashboard.password.update'), [
        'password' => 'RahasiaBaru#2026',
        'password_confirmation' => 'RahasiaBaru#2026',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    expect(session('pasak_dashboard_needs_password_change'))->toBeNull();

    // Verifikasi hash di tabel pasak_settings
    $storedHash = PasakSetting::query()
        ->where('key', PasakDashboardSecret::DB_PASSWORD_HASH_KEY)
        ->value('value');

    $expectedHash = hash_hmac('sha256', 'RahasiaBaru#2026', $secret);

    expect($storedHash)->not->toBeNull()
        ->and($storedHash)->toBe($expectedHash);

    // Sekarang coba login dengan password lama (Admin) -> harus ditolak
    $failedLogin = $this->post(route('pasak.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'Admin',
    ]);
    $failedLogin->assertSessionHasErrors('password');

    // Coba login dengan password baru -> harus berhasil dan tidak meminta ubah password lagi
    $successLogin = $this->post(route('pasak.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'RahasiaBaru#2026',
    ]);
    $successLogin->assertRedirect('/pasak');
    expect(session('pasak_dashboard_authenticated'))->toBeTrue()
        ->and(session('pasak_dashboard_needs_password_change'))->toBeNull();
});

it('allows user to logout and clears dashboard authentication session', function () {
    $response = $this->withSession(['pasak_dashboard_authenticated' => true])
        ->post(route('pasak.dashboard.logout'));

    $response->assertRedirect(route('pasak.dashboard.login'));
    expect(session('pasak_dashboard_authenticated'))->toBeNull();

    // Akses dashboard setelah logout harus kembali dialihkan ke login
    $afterResponse = $this->get('/pasak');
    $afterResponse->assertRedirect(route('pasak.dashboard.login'));
});

it('rate limits brute force login attempts', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('pasak.dashboard.login.submit'), [
            'username' => 'admin',
            'password' => 'wrong-secret',
        ]);
    }

    // Percobaan ke-6 harus kena rate limit
    $response = $this->post(route('pasak.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'wrong-secret',
    ]);

    $response->assertSessionHasErrors('username');
    $errors = session('errors')->get('username');
    expect($errors[0])->toContain('Terlalu banyak percobaan login');
});

it('allows emergency recovery login with PASAK_DASHBOARD_SECRET when user forgets custom password', function () {
    $secret = 'test-secret-key-1234567890abcdefghijklmnopqrstuvwxyz';

    // Set custom password di database
    PasakDashboardSecret::storePasswordInDatabase('CustomSecurePassword123!', $secret);

    // Pengguna lupa password dan mencoba login menggunakan PASAK_DASHBOARD_SECRET darurat
    $response = $this->post(route('pasak.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => $secret,
    ]);

    $response->assertRedirect('/pasak');
    expect(session('pasak_dashboard_authenticated'))->toBeTrue()
        ->and(session('pasak_dashboard_is_emergency_login'))->toBeTrue()
        ->and(session('pasak_dashboard_needs_password_change'))->toBeTrue();

    // Di dasbor, banner darurat aktif
    $dashResponse = $this->withSession([
        'pasak_dashboard_authenticated' => true,
        'pasak_dashboard_is_emergency_login' => true,
        'pasak_dashboard_needs_password_change' => true,
    ])->get('/pasak');

    $dashResponse->assertOk();
    expect($dashResponse->getContent())->toContain('PASAK_DASHBOARD_SECRET');
});

it('renders password update section and recovery guide on the settings page', function () {
    $response = $this->withSession([
        'pasak_dashboard_authenticated' => true,
    ])->get(route('pasak.dashboard.settings'));

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('password-section')
        ->and($content)->toContain('settings_new_password')
        ->and($content)->toContain('settings_confirm_password')
        ->and($content)->toContain('PASAK_DASHBOARD_SECRET')
        ->and($content)->toContain('P@sak#VPS2026!');
});
