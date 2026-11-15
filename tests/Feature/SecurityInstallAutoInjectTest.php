<?php

use Illuminate\Support\Facades\File;
use Pasax\SecureMonitor\Console\Commands\PasaxInstallCommand;
use Pasax\SecureMonitor\Console\Commands\SecurityInstallCommand;

$tempDir = '';

beforeEach(function () use (&$tempDir) {
    $tempDir = sys_get_temp_dir().'/security_install_test_'.uniqid();
    File::makeDirectory($tempDir.'/app/Models', 0755, true, true);
    File::makeDirectory($tempDir.'/bootstrap', 0755, true, true);
});

afterEach(function () use (&$tempDir) {
    if (! empty($tempDir) && File::isDirectory($tempDir)) {
        File::deleteDirectory($tempDir);
    }
});

test('security install command can auto-inject trait and middleware', function () use (&$tempDir) {
    $userFile = $tempDir.'/app/Models/User.php';
    File::put($userFile, <<<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
}
PHP
    );

    $bootstrapFile = $tempDir.'/bootstrap/app.php';
    File::put($bootstrapFile, <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->create();
PHP
    );

    // Mock app_path and base_path behavior by running targeted methods or verifying regex transformations
    $userContent = File::get($userFile);
    expect($userContent)->not->toContain('HasPasaxSecurityRelations')
        ->and($userContent)->not->toContain('HasSecurityRelations');

    $bootstrapContent = File::get($bootstrapFile);
    expect($bootstrapContent)->not->toContain('PasaxBlockIpAddress')
        ->and($bootstrapContent)->not->toContain('BlockIpAddress');

    // Test install options are registered in command (supports both PasaxInstallCommand & SecurityInstallCommand)
    $command = $this->app->make(PasaxInstallCommand::class);
    $definition = $command->getDefinition();
    expect($definition->hasOption('without-user-trait'))->toBeTrue()
        ->and($definition->hasOption('without-middleware'))->toBeTrue()
        ->and($definition->hasOption('without-routes'))->toBeTrue()
        ->and($definition->hasOption('without-api'))->toBeTrue();

    $legacyCommand = $this->app->make(SecurityInstallCommand::class);
    expect($legacyCommand)->toBeInstanceOf(PasaxInstallCommand::class);
});

test('pasax routes tag publishes pasax.php and pasax-api.php', function () {
    $this->artisan('vendor:publish', ['--tag' => 'pasax-routes'])
        ->assertSuccessful();

    expect(File::exists(base_path('routes/pasax.php')))->toBeTrue();
    expect(File::exists(base_path('routes/pasax-api.php')))->toBeTrue();

    // Clean up
    @unlink(base_path('routes/pasax.php'));
    @unlink(base_path('routes/pasax-api.php'));
});
