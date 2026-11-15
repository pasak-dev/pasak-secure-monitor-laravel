<?php

namespace Pasax\SecureMonitor\Tests;

use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Pasax\SecureMonitor\Http\Middleware\BlockIpAddress;
use Pasax\SecureMonitor\Http\Middleware\DetectSecurityThreats;
use Pasax\SecureMonitor\PasaxServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        // pasax:install publishes config/pasax.php into the Testbench skeleton.
        // Left behind, that copy shadows the package config in every later
        // test (and later runs), so edits to config/pasax.php are ignored.
        foreach (['config/pasax.php', 'routes/pasax.php', 'routes/pasax-api.php', 'routes/pasax-service.php'] as $rel) {
            $p = dirname(__DIR__).'/vendor/orchestra/testbench-core/laravel/'.$rel;
            if (is_file($p)) {
                unlink($p);
            }
        }

        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [PasaxServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('auth.providers.users.model', TestUser::class);
        $app['config']->set('pasax.user_model', TestUser::class);
        $app['config']->set('pasax.enabled', true);
        $app['config']->set('pasax.block_enforcement', true);
        $app['config']->set('pasax.routes.enabled', true);

        $kernel = $app->make(KernelContract::class);

        if ($kernel instanceof HttpKernel) {
            $kernel->pushMiddleware(BlockIpAddress::class);
            $kernel->pushMiddleware(DetectSecurityThreats::class);
        }
    }

    protected function defineRoutes($router): void
    {
        $router->post('/login', function () {
            return redirect('/');
        });
        $router->get('/login', function () {
            return response('login form', 200);
        });
        $router->get('/', function () {
            return response('home', 200);
        });
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('user');
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function tearDown(): void
    {
        $base = base_path();
        $testbenchBase = dirname(__DIR__).'/vendor/orchestra/testbench-core/laravel';

        if (realpath($base) === realpath($testbenchBase)) {
            foreach (['.env', '.env.example'] as $envFile) {
                $path = $base.'/'.$envFile;

                if (File::exists($path)) {
                    File::put($path, "APP_NAME=Laravel\n");
                }
            }
        }

        parent::tearDown();
    }

    protected function createAdminUser(array $attributes = []): TestUser
    {
        return TestUser::create(
            array_merge(
                [
                    'name' => 'Admin User',
                    'email' => 'admin@example.com',
                    'password' => bcrypt('password'),
                    'role' => 'admin',
                ],
                $attributes,
            ),
        );
    }

    protected function createRegularUser(array $attributes = []): TestUser
    {
        return TestUser::create(
            array_merge(
                [
                    'name' => 'Regular User',
                    'email' => 'user@example.com',
                    'password' => bcrypt('password'),
                    'role' => 'user',
                ],
                $attributes,
            ),
        );
    }
}
