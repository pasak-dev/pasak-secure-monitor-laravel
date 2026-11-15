<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Pasax\SecureMonitor\Models\PasaxSetting;
use Pasax\SecureMonitor\Support\PasaxDashboardSecret;

beforeEach(function () {
    config()->set('pasax.dashboard.enabled', true);
    config()->set('pasax.dashboard.auth.enabled', true);
    config()->set('pasax.dashboard.auth.username', 'admin');
    config()->set('pasax.dashboard.auth.default_password', 'Admin');
    config()->set('pasax.dashboard.rotating', false);

    // Salin stub views ke resource_path
    $viewsTarget = resource_path('views/pages/pasax');
    File::ensureDirectoryExists($viewsTarget);
    File::copyDirectory(
        dirname(__DIR__, 2).'/stubs/blade/pages/pasax',
        $viewsTarget,
    );

    // Pastikan update-password-modal ada di views
    if (! File::exists($viewsTarget.'/update-password-modal.blade.php')) {
        File::copy(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasax/update-password-modal.blade.php',
            $viewsTarget.'/update-password-modal.blade.php',
        );
    }

    // Muat rute dashboard blade
    require __DIR__.'/../../routes/pasax-web-blade.php';
    Route::getRoutes()->refreshNameLookups();

    // Buat .env untuk testing dengan kredensial pasax
    $envPath = base_path('.env');
    $secret = 'test-secret-key-1234567890abcdefghijklmnopqrstuvwxyz';
    File::put(
        $envPath,
        "PASAX_DASHBOARD_USERNAME=admin\nPASAX_DASHBOARD_PASSWORD=Admin\nPASAX_DASHBOARD_SECRET={$secret}\n",
    );

    // Bersihkan setting hash lama di database
    PasaxSetting::query()->where('key', PasaxDashboardSecret::DB_PASSWORD_HASH_KEY)->delete();
});

afterEach(function () {
    $envPath = base_path('.env');
    if (File::exists($envPath)) {
        File::delete($envPath);
    }
});

it('redirects unauthenticated guest accessing dashboard to pasax login', function () {
    $response = $this->get('/pasax');

    $response->assertRedirect(route('pasax.dashboard.login'));
});

it('renders the pasax login form with username and password fields', function () {
    $response = $this->get(route('pasax.dashboard.login'));

    $response->assertOk();
    $response->assertSee('name="username"', false);
    $response->assertSee('name="password"', false);
    $response->assertSee('sec-header-logo-light', false);
    $response->assertSee('sec-header-logo-dark', false);
    $response->assertSee('PASAX_DASHBOARD_SECRET');
});

it('rejects invalid login credentials and stays unauthenticated', function () {
    $response = $this->post(route('pasax.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'wrong-secret-key',
    ]);

    $response->assertSessionHasErrors('password');
    expect(session('pasax_dashboard_authenticated'))->toBeNull();
});

it('authenticates user with default password Admin and flags session for password change', function () {
    $response = $this->post(route('pasax.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'Admin',
    ]);

    $response->assertRedirect('/pasax');
    expect(session('pasax_dashboard_authenticated'))->toBeTrue()
        ->and(session('pasax_dashboard_needs_password_change'))->toBeTrue();

    // Akses dasbor menampilkan peringatan ganti password
    $dashResponse = $this->withSession([
        'pasax_dashboard_authenticated' => true,
        'pasax_dashboard_needs_password_change' => true,
    ])->get('/pasax');

    $dashResponse->assertOk();
    expect($dashResponse->getContent())->toContain('pasaxUpdatePasswordModal');
    $dashResponse->assertSee('pasaxUpdatePasswordModal');
});

it('authenticates user with secret key directly as fallback', function () {
    $response = $this->post(route('pasax.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'test-secret-key-1234567890abcdefghijklmnopqrstuvwxyz',
    ]);

    $response->assertRedirect('/pasax');
    expect(session('pasax_dashboard_authenticated'))->toBeTrue();
});

it('allows updating dashboard password and stores HMAC hash in database', function () {
    $secret = 'test-secret-key-1234567890abcdefghijklmnopqrstuvwxyz';

    // Kirim request update password
    $response = $this->withSession([
        'pasax_dashboard_authenticated' => true,
        'pasax_dashboard_needs_password_change' => true,
    ])->post(route('pasax.dashboard.password.update'), [
        'password' => 'RahasiaBaru#2026',
        'password_confirmation' => 'RahasiaBaru#2026',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    expect(session('pasax_dashboard_needs_password_change'))->toBeNull();

    // Verifikasi hash di tabel pasax_settings
    $storedHash = PasaxSetting::query()
        ->where('key', PasaxDashboardSecret::DB_PASSWORD_HASH_KEY)
        ->value('value');

    $expectedHash = hash_hmac('sha256', 'RahasiaBaru#2026', $secret);

    expect($storedHash)->not->toBeNull()
        ->and($storedHash)->toBe($expectedHash);

    // Sekarang coba login dengan password lama (Admin) -> harus ditolak
    $failedLogin = $this->post(route('pasax.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'Admin',
    ]);
    $failedLogin->assertSessionHasErrors('password');

    // Coba login dengan password baru -> harus berhasil dan tidak meminta ubah password lagi
    $successLogin = $this->post(route('pasax.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'RahasiaBaru#2026',
    ]);
    $successLogin->assertRedirect('/pasax');
    expect(session('pasax_dashboard_authenticated'))->toBeTrue()
        ->and(session('pasax_dashboard_needs_password_change'))->toBeNull();
});

it('allows user to logout and clears dashboard authentication session', function () {
    $response = $this->withSession(['pasax_dashboard_authenticated' => true])
        ->post(route('pasax.dashboard.logout'));

    $response->assertRedirect(route('pasax.dashboard.login'));
    expect(session('pasax_dashboard_authenticated'))->toBeNull();

    // Akses dashboard setelah logout harus kembali dialihkan ke login
    $afterResponse = $this->get('/pasax');
    $afterResponse->assertRedirect(route('pasax.dashboard.login'));
});

it('rate limits brute force login attempts', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('pasax.dashboard.login.submit'), [
            'username' => 'admin',
            'password' => 'wrong-secret',
        ]);
    }

    // Percobaan ke-6 harus kena rate limit
    $response = $this->post(route('pasax.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => 'wrong-secret',
    ]);

    $response->assertSessionHasErrors('username');
    $errors = session('errors')->get('username');
    expect($errors[0])->toContain('Terlalu banyak percobaan login');
});

it('allows emergency recovery login with PASAX_DASHBOARD_SECRET when user forgets custom password', function () {
    $secret = 'test-secret-key-1234567890abcdefghijklmnopqrstuvwxyz';

    // Set custom password di database
    PasaxDashboardSecret::storePasswordInDatabase('CustomSecurePassword123!', $secret);

    // Pengguna lupa password dan mencoba login menggunakan PASAX_DASHBOARD_SECRET darurat
    $response = $this->post(route('pasax.dashboard.login.submit'), [
        'username' => 'admin',
        'password' => $secret,
    ]);

    $response->assertRedirect('/pasax');
    expect(session('pasax_dashboard_authenticated'))->toBeTrue()
        ->and(session('pasax_dashboard_is_emergency_login'))->toBeTrue()
        ->and(session('pasax_dashboard_needs_password_change'))->toBeTrue();

    // Di dasbor, banner darurat aktif
    $dashResponse = $this->withSession([
        'pasax_dashboard_authenticated' => true,
        'pasax_dashboard_is_emergency_login' => true,
        'pasax_dashboard_needs_password_change' => true,
    ])->get('/pasax');

    $dashResponse->assertOk();
    expect($dashResponse->getContent())->toContain('PASAX_DASHBOARD_SECRET');
});

it('renders password update section and recovery guide on the settings page', function () {
    $response = $this->withSession([
        'pasax_dashboard_authenticated' => true,
    ])->get(route('pasax.dashboard.settings'));

    $response->assertOk();
    $content = $response->getContent();
    expect($content)->toContain('password-section')
        ->and($content)->toContain('settings_new_password')
        ->and($content)->toContain('settings_confirm_password')
        ->and($content)->toContain('PASAX_DASHBOARD_SECRET')
        ->and($content)->toContain('P@sak#VPS2026!');
});
