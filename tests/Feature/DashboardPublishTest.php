<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

afterEach(function () {
    $directories = [
        resource_path('views/pages/pasax'),
        resource_path('js/pages/pasax'),
        resource_path('js/components/pasax'),
    ];

    foreach ($directories as $directory) {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }
    }
});

test(
    'dashboard is disabled by default so the package stays headless',
    function () {
        expect(config('pasax.dashboard.enabled'))
            ->toBeFalse()
            ->and(config('pasax.dashboard.driver'))
            ->toBe('blade')
            ->and(config('pasax.dashboard.prefix'))
            ->toBe('pasax')
            ->and(config('pasax.dashboard.middleware'))
            ->toContain('auth');
    },
);

test(
    'plain views tag publishes the blade monitoring views',
    function () {
        $directory = resource_path('views/pages/pasax');

        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }

        $this->artisan('vendor:publish', [
            '--tag' => 'plain-views',
            '--force' => true,
        ])->assertSuccessful();

        expect(File::exists($directory.'/overview.blade.php'))
            ->toBeTrue()
            ->and(File::exists($directory.'/logs.blade.php'))
            ->toBeTrue()
            ->and(File::exists($directory.'/blocked-ips.blade.php'))
            ->toBeTrue()
            ->and(File::exists($directory.'/server.blade.php'))
            ->toBeTrue()
            ->and(File::exists($directory.'/sessions.blade.php'))
            ->toBeTrue()
            ->and(File::exists($directory.'/tickets.blade.php'))
            ->toBeTrue()
            ->and(File::exists($directory.'/layout.blade.php'))
            ->toBeTrue();

        expect(File::get($directory.'/overview.blade.php'))->toContain(
            'Security Overview',
        );
    },
);

test(
    'pasax dashboard tag publishes the blade views and configuration',
    function () {
        $directory = resource_path('views/pages/pasax');

        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }

        $this->artisan('vendor:publish', [
            '--tag' => 'pasax-web',
            '--force' => true,
        ])->assertSuccessful();

        expect(File::exists($directory.'/overview.blade.php'))->toBeTrue();
    },
);

test(
    'blade dashboard routes are gated behind authentication',
    function () {
        $routes = File::get(__DIR__.'/../../routes/pasax-web-blade.php');

        expect($routes)
            ->toContain('pasax.dashboard.middleware')
            ->and($routes)
            ->toContain('OverviewController::class')
            ->and($routes)
            ->toContain('pasax.dashboard.');

        expect(Route::has('pasax.dashboard.overview'))->toBeFalse();
    },
);

test(
    'security install command can publish the dashboard with the with-dashboard flag',
    function () {
        $directory = resource_path('views/pages/pasax');

        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }

        $this->artisan('pasax:install', [
            '--force' => true,
            '--with-dashboard' => true,
            '--without-nginx' => true,
            '--without-htaccess' => true,
            '--without-env' => true,
        ])->assertSuccessful();

        expect(File::exists($directory.'/overview.blade.php'))->toBeTrue();
    },
);

test(
    'install with dashboard removes react leftovers so only blade exists',
    function () {
        $bladeDir = resource_path('views/pages/pasax');
        $tsxDir = resource_path('js/pages/pasax');
        $componentsDir = resource_path('js/components/pasax');

        File::makeDirectory($tsxDir, 0755, true, true);
        File::makeDirectory($componentsDir, 0755, true, true);
        File::put($tsxDir.'/stale.tsx', 'stale');
        File::put($componentsDir.'/stale.tsx', 'stale');

        $this->artisan('pasax:install', [
            '--force' => true,
            '--with-dashboard' => true,
            '--without-nginx' => true,
            '--without-htaccess' => true,
            '--without-env' => true,
        ])->assertSuccessful();

        expect(File::exists($bladeDir.'/overview.blade.php'))->toBeTrue()
            ->and(File::isDirectory($tsxDir))->toBeFalse()
            ->and(File::isDirectory($componentsDir))->toBeFalse();
    },
);

test(
    'install with api-only removes the dashboard views',
    function () {
        $bladeDir = resource_path('views/pages/pasax');
        $tsxDir = resource_path('js/pages/pasax');

        File::makeDirectory($bladeDir, 0755, true, true);
        File::makeDirectory($tsxDir, 0755, true, true);
        File::put($bladeDir.'/stale.blade.php', 'stale');
        File::put($tsxDir.'/stale.tsx', 'stale');

        $this->artisan('pasax:install', [
            '--force' => true,
            '--api-only' => true,
            '--without-nginx' => true,
            '--without-htaccess' => true,
            '--without-env' => true,
        ])->assertSuccessful();

        expect(File::isDirectory($bladeDir))->toBeFalse()
            ->and(File::isDirectory($tsxDir))->toBeFalse();
    },
);

test(
    'install with dashboard publishes blade-only web routes without inertia or livewire',
    function () {
        $webRouteFile = base_path('routes/pasax.php');
        @unlink($webRouteFile);

        try {
            $this->artisan('pasax:install', [
                '--force' => true,
                '--with-dashboard' => true,
                '--without-nginx' => true,
                '--without-htaccess' => true,
                '--without-env' => true,
            ])->assertSuccessful();

            $routes = File::get($webRouteFile);

            expect($routes)->toContain('OverviewController::class')
                ->and($routes)->toContain('resources/views/pages/pasax')
                ->and($routes)->not->toContain('Inertia\Inertia::class')
                ->and($routes)->not->toContain('Livewire\Livewire::class')
                ->and($routes)->not->toContain('Route::livewire');
        } finally {
            @unlink($webRouteFile);
        }
    },
);
