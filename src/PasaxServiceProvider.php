<?php

namespace Pasax\SecureMonitor;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Pasax\SecureMonitor\Console\Commands\PasaxCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxBaselineCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxCloudCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxDeployCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxInstallCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxPruneSecurityLogsCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxPurgeInjectedDataCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxScanAccessLogsCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxSecretCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxSentinelCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxUnblockIpCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxUninstallCommand;
use Pasax\SecureMonitor\Console\Commands\PasaxUpgradeCommand;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxServerSecurityApiController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxServerController;
use Pasax\SecureMonitor\Http\Middleware\PasaxBlockIpAddress;
use Pasax\SecureMonitor\Http\Middleware\PasaxDashboardAuthenticate;
use Pasax\SecureMonitor\Http\Middleware\PasaxDetectSecurityThreats;
use Pasax\SecureMonitor\Http\Middleware\PasaxEnsureSecurityAdmin;
use Pasax\SecureMonitor\Http\Middleware\PasaxInjectAnnouncementBanner;
use Pasax\SecureMonitor\Http\Middleware\PasaxRotatingDashboard;
use Pasax\SecureMonitor\Http\Middleware\PasaxShareDashboardSlug;
use Pasax\SecureMonitor\Http\Middleware\PasaxTrackUserActivity;
use Pasax\SecureMonitor\Listeners\PasaxLogFailedLoginAttempt;
use Pasax\SecureMonitor\Listeners\PasaxRecordUserLogin;
use Pasax\SecureMonitor\Listeners\PasaxResetLoginAttempts;
use Pasax\SecureMonitor\Services\AccessLogScannerService;
use Pasax\SecureMonitor\Services\LoginThrottleService;
use Pasax\SecureMonitor\Services\PasaxAccessLogScannerService;
use Pasax\SecureMonitor\Services\PasaxCloudService;
use Pasax\SecureMonitor\Services\PasaxLoginThrottleService;
use Pasax\SecureMonitor\Services\PasaxSentinelBridge;
use Pasax\SecureMonitor\Services\PasaxServerSecurityService;
use Pasax\SecureMonitor\Services\PasaxService;
use Pasax\SecureMonitor\Services\PasaxUserLoginService;
use Pasax\SecureMonitor\Services\PasaxVersionCheckService;
use Pasax\SecureMonitor\Services\ServerSecurityService;
use Pasax\SecureMonitor\Services\UserLoginService;
use Pasax\SecureMonitor\Services\VersionCheckService;

class PasaxServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/pasax.php', 'pasax');

        // Backward compatibility: old installs publish config/pasax.php
        // with SECURITY_* env vars. Merge it as fallback for missing keys.
        $legacyConfig = config('security');
        if (is_array($legacyConfig)) {
            foreach ($legacyConfig as $key => $value) {
                if (! config()->has('pasax.'.$key)) {
                    config()->set('pasax.'.$key, $value);
                }
            }
            // Keep the legacy key in sync so old config('security.*') calls keep working.
            config()->set(
                'security',
                array_replace($legacyConfig, config('pasax')),
            );
        } else {
            config()->set('security', config('pasax'));
        }

        $this->app->singleton(PasaxService::class, function ($app) {
            return new PasaxService;
        });

        $this->app->singleton(PasaxSentinelBridge::class, function ($app) {
            return new PasaxSentinelBridge;
        });

        $this->app->singleton(PasaxCloudService::class, function ($app) {
            return new PasaxCloudService;
        });

        $this->app->singleton(PasaxLoginThrottleService::class, function (
            $app,
        ) {
            return new PasaxLoginThrottleService(
                $app->make(PasaxService::class),
            );
        });
        $this->app->alias(
            PasaxLoginThrottleService::class,
            LoginThrottleService::class,
        );

        $this->app->singleton(PasaxUserLoginService::class, function ($app) {
            return new PasaxUserLoginService;
        });
        $this->app->alias(
            PasaxUserLoginService::class,
            UserLoginService::class,
        );

        $this->app->singleton(PasaxServerSecurityService::class, function (
            $app,
        ) {
            return new PasaxServerSecurityService(
                $app->make(PasaxService::class),
                $app->make(PasaxLoginThrottleService::class),
            );
        });
        $this->app->alias(
            PasaxServerSecurityService::class,
            ServerSecurityService::class,
        );

        $this->app->singleton(PasaxAccessLogScannerService::class, function (
            $app,
        ) {
            return new PasaxAccessLogScannerService(
                $app->make(PasaxService::class),
            );
        });
        $this->app->alias(
            PasaxAccessLogScannerService::class,
            AccessLogScannerService::class,
        );

        $this->app->singleton(PasaxVersionCheckService::class, function () {
            return new PasaxVersionCheckService;
        });
        $this->app->alias(
            PasaxVersionCheckService::class,
            VersionCheckService::class,
        );

        $this->app->alias(PasaxService::class, 'pasax');
        $this->app->alias(PasaxVersionCheckService::class, 'pasax.version');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadJsonTranslationsFrom(__DIR__.'/../lang');
        $this->registerPublishing();
        $this->registerCommands();
        $this->registerListeners();
        $this->registerRoutes();
        $this->registerDashboardRoutes();
        $this->registerDashboardUrlDefaults();
        $this->registerSchedule();
        $this->registerGate();
        $this->registerMiddleware();
        $this->registerVersionCheck();
    }

    protected function registerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Config
        $this->publishes(
            [
                __DIR__.'/../config/pasax.php' => config_path('pasax.php'),
            ],
            'pasax-config',
        );

        // Migrations
        $this->publishes(
            [
                __DIR__.'/../database/migrations' => database_path(
                    'migrations',
                ),
            ],
            'pasax-migrations',
        );

        // Nginx Hardened Configuration
        $this->publishes(
            [
                __DIR__.'/../stubs/nginx.conf.stub' => base_path(
                    'nginx.conf',
                ),
            ],
            'pasax-nginx',
        );

        // Apache 2 VirtualHost Hardened Configuration
        $this->publishes(
            [
                __DIR__.'/../stubs/apache2.conf.stub' => base_path(
                    'apache2.conf',
                ),
            ],
            'pasax-apache',
        );

        // Apache .htaccess Hardened Configuration
        $this->publishes(
            [
                __DIR__.'/../stubs/htaccess.stub' => public_path('.htaccess'),
            ],
            'pasax-htaccess',
        );

        // Default 403 "blocked" and 429 "pasax-login-throttle" error pages
        $this->publishes(
            [
                __DIR__.'/../stubs/blocked.blade.php' => resource_path(
                    'views/errors/blocked.blade.php',
                ),
                __DIR__.'/../stubs/pasax-login-throttle.blade.php' => resource_path(
                    'views/errors/pasax-login-throttle.blade.php',
                ),
            ],
            'pasax-views',
        );

        // Publish All Assets (Config, Migrations, Nginx, Apache2, Htaccess, Blocked View)
        $this->publishes(
            [
                __DIR__.'/../config/pasax.php' => config_path('pasax.php'),
                __DIR__.'/../database/migrations' => database_path(
                    'migrations',
                ),
                __DIR__.'/../stubs/nginx.conf.stub' => base_path(
                    'nginx.conf',
                ),
                __DIR__.'/../stubs/apache2.conf.stub' => base_path(
                    'apache2.conf',
                ),
                __DIR__.'/../stubs/htaccess.stub' => public_path('.htaccess'),
                __DIR__.'/../stubs/blocked.blade.php' => resource_path(
                    'views/errors/blocked.blade.php',
                ),
                __DIR__.'/../stubs/pasax-login-throttle.blade.php' => resource_path(
                    'views/errors/pasax-login-throttle.blade.php',
                ),
            ],
            'pasax-all',
        );

        // Pasax Route Stubs (routes/pasax.php & routes/pasax-api.php)
        $pasaxRouteStubs = [
            __DIR__.'/../stubs/routes/pasax.php.stub' => base_path(
                'routes/pasax.php',
            ),
            __DIR__.'/../stubs/routes/pasax-api.php.stub' => base_path(
                'routes/pasax-api.php',
            ),
        ];
        $this->publishes($pasaxRouteStubs, 'pasax-routes');

        $this->publishes(
            [
                __DIR__.'/../stubs/routes/pasax.php.stub' => base_path(
                    'routes/pasax.php',
                ),
            ],
            'pasax-routes-web',
        );

        $this->publishes(
            [
                __DIR__.'/../stubs/routes/pasax-api.php.stub' => base_path(
                    'routes/pasax-api.php',
                ),
            ],
            'pasax-routes-api',
        );

        // Dashboard monitoring Blade murni (views + konfigurasi).
        // Satu-satunya dashboard yang didukung: Blade murni tanpa
        // Livewire/Inertia/Vue — 100% Pure PHP (standar Spatie).
        $bladeViews = [
            __DIR__.'/../stubs/blade/pages/pasax' => resource_path(
                'views/pages/pasax',
            ),
        ];
        $dashboardAssets = array_merge($bladeViews, [
            __DIR__.'/../config/pasax.php' => config_path('pasax.php'),
        ]);

        $this->publishes($dashboardAssets, 'pasax-web');
        $this->publishes($dashboardAssets, 'plain-views');
        $this->publishes($bladeViews, 'pasax-web-views');

        // AI Agent Skills (.agents/skills/pasax/SKILL.md)
        $this->publishes(
            [
                __DIR__.'/../stubs/skills/pasax/SKILL.md' => base_path(
                    '.agents/skills/pasax/SKILL.md',
                ),
            ],
            'pasax-skills',
        );

        // Language translations
        $this->publishes(
            [
                __DIR__.'/../lang' => $this->app->langPath('vendor/pasax'),
            ],
            'pasax-lang',
        );

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            PasaxCommand::class,
            PasaxInstallCommand::class,
            PasaxUninstallCommand::class,
            PasaxUpgradeCommand::class,
            PasaxPruneSecurityLogsCommand::class,
            PasaxUnblockIpCommand::class,
            PasaxScanAccessLogsCommand::class,
            PasaxBaselineCommand::class,
            PasaxPurgeInjectedDataCommand::class,
            PasaxSecretCommand::class,
            PasaxDeployCommand::class,
            PasaxSentinelCommand::class,
            PasaxCloudCommand::class,
        ]);
    }

    protected function registerListeners(): void
    {
        Event::listen(Failed::class, PasaxLogFailedLoginAttempt::class);
        Event::listen(Login::class, PasaxResetLoginAttempts::class);
        Event::listen(Login::class, PasaxRecordUserLogin::class);
    }

    protected function registerRoutes(): void
    {
        if (! config('pasax.routes.enabled', true)) {
            return;
        }

        // Jika rute sudah didaftarkan (misalnya oleh routes/api.php di host), hindari duplikasi
        if (Route::has('pasax.logs.index')) {
            $this->ensureEssentialApiRoutes();

            return;
        }

        // Jika berkas rute kustom host ada, prioritaskan pemuatannya
        $hostRouteFile = base_path('routes/pasax-api.php');
        if (! file_exists($hostRouteFile)) {
            $hostRouteFile = base_path('routes/pasax-service.php');
        }
        if (file_exists($hostRouteFile)) {
            $this->loadRoutesFrom($hostRouteFile);
            $this->ensureEssentialApiRoutes();

            return;
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/pasax.php');
    }

    /**
     * Register the Blade monitoring dashboard (the only dashboard driver).
     *
     * The dashboard is only wired up when it has been explicitly enabled via
     * "pasax.dashboard.enabled". Its views are published separately using:
     *
     *     php artisan pasax:install --with-dashboard
     */
    protected function registerDashboardRoutes(): void
    {
        if (! config('pasax.dashboard.enabled', false)) {
            return;
        }

        // Jika rute dashboard sudah didaftarkan (misalnya oleh routes/web.php di host), hindari duplikasi
        if (Route::has('pasax.dashboard.overview')) {
            $this->ensureEssentialDashboardRoutes();

            return;
        }

        // Jika berkas rute web kustom host ada, prioritaskan pemuatannya
        $hostDashboardFile = base_path('routes/pasax.php');
        if (file_exists($hostDashboardFile)) {
            $this->loadRoutesFrom($hostDashboardFile);
            $this->ensureEssentialDashboardRoutes();

            return;
        }

        $this->registerBladeDashboardRoutes();
    }

    /**
     * Register the plain-Blade dashboard routes (no Livewire/Inertia needed).
     */
    protected function registerBladeDashboardRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/pasax-web-blade.php');
    }

    /**
     * Pastikan rute esensial API baru (seperti lokasi server) tetap terdaftar
     * meskipun host menggunakan berkas routes/pasax-service.php dari versi lama.
     */
    protected function ensureEssentialApiRoutes(): void
    {
        if (Route::has('pasax.server.location')) {
            return;
        }

        $prefix = config('pasax.routes.prefix', 'api/pasax');
        $middleware = config('pasax.routes.middleware', ['api']);

        Route::prefix($prefix)
            ->middleware($middleware)
            ->name('pasax.')
            ->group(function () {
                Route::get('server/location', [
                    PasaxServerSecurityApiController::class,
                    'getLocation',
                ])->name('server.location.get');
                Route::post('server/location', [
                    PasaxServerSecurityApiController::class,
                    'updateLocation',
                ])->name('server.location');
                Route::get('server/report/export', [
                    PasaxServerSecurityApiController::class,
                    'exportReport',
                ])->name('server.report.export');
            });
    }

    /**
     * Pastikan rute esensial dashboard baru (seperti konfigurasi lokasi & waktu server)
     * tetap terdaftar meskipun host menggunakan berkas routes/pasax.php dari versi lama.
     */
    protected function ensureEssentialDashboardRoutes(): void
    {
        if (Route::has('pasax.dashboard.server.location')) {
            return;
        }

        $prefix = trim((string) config('pasax.dashboard.prefix', 'pasax'), '/');
        if ($prefix === '') {
            $prefix = 'pasax';
        }

        $configuredMiddleware = array_filter(
            (array) config('pasax.dashboard.middleware', ['web', 'auth']),
        );
        $authEnabled = (bool) config('pasax.dashboard.auth.enabled', true);

        $middleware = [];
        foreach ($configuredMiddleware as $m) {
            if ($m === 'auth' && $authEnabled) {
                $middleware[] = PasaxDashboardAuthenticate::class;
            } else {
                $middleware[] = $m;
            }
        }

        if (
            $authEnabled &&
            ! in_array(PasaxDashboardAuthenticate::class, $middleware, true)
        ) {
            $middleware[] = PasaxDashboardAuthenticate::class;
        }

        $middleware = array_values(array_unique(array_filter($middleware)));
        $adminMiddleware = array_filter(
            (array) config('pasax.dashboard.admin_middleware', []),
        );
        $rotating = (bool) config('pasax.dashboard.rotating', false);

        $registerRoutes = function () {
            Route::post('server', [
                PasaxServerController::class,
                'updateLocation',
            ]);
            Route::post('server/location', [
                PasaxServerController::class,
                'updateLocation',
            ])->name('server.location');
            Route::get('server/report/export', [
                PasaxServerController::class,
                'exportReport',
            ])->name('server.report.export');
            Route::get('reports', [
                PasaxServerController::class,
                'reportPage',
            ])->name('reports');
        };

        if ($rotating) {
            Route::middleware(
                array_merge($middleware, $adminMiddleware, [
                    PasaxRotatingDashboard::class,
                ]),
            )
                ->prefix('{pasaxSlug}')
                ->where([
                    'pasaxSlug' => preg_quote($prefix, '/').'-[A-Za-z0-9]{12,32}',
                ])
                ->name('pasax.dashboard.')
                ->group($registerRoutes);
        } else {
            Route::middleware(array_merge($middleware, $adminMiddleware))
                ->prefix($prefix)
                ->name('pasax.dashboard.')
                ->group($registerRoutes);
        }
    }

    /**
     * Sediakan fallback URL::defaults agar route() dashboard tidak fatal di
     * luar konteks dashboard (view host, API, console, mail). URL stabil
     * yang dihasilkan ditangani alias redirect ke slug aktif.
     */
    protected function registerDashboardUrlDefaults(): void
    {
        if (! (bool) config('pasax.dashboard.rotating', false)) {
            return;
        }

        $base = trim((string) config('pasax.dashboard.prefix', 'pasax'), '/');

        if ($base === '') {
            $base = 'pasax';
        }

        URL::defaults(['pasaxSlug' => $base]);

        try {
            $router = $this->app->make(Router::class);
            $router->pushMiddlewareToGroup(
                'web',
                PasaxShareDashboardSlug::class,
            );
        } catch (\Throwable) {
            // Abaikan bila grup web tidak tersedia.
        }
    }

    protected function registerSchedule(): void
    {
        if (! config('pasax.schedule.enabled', true)) {
            return;
        }

        $this->app->booted(function (): void {
            if (! $this->app->runningInConsole()) {
                return;
            }

            try {
                $schedule = $this->app->make(Schedule::class);

                // Daily log prune
                $pruneAt = config('pasax.schedule.prune_at', '02:30');
                $schedule->command('pasax:prune-logs')->dailyAt($pruneAt);

                // Scheduler Heartbeat
                $schedule
                    ->call(function (): void {
                        Cache::put(
                            PasaxServerSecurityService::HEARTBEAT_KEY,
                            now()->toIso8601String(),
                            now()->addHour(),
                        );
                    })
                    ->everyMinute()
                    ->name('pasax-heartbeat');

                // Access log auto-scan
                if (config('pasax.access_log.auto_scan_enabled', false)) {
                    $hours = max(
                        1,
                        (int) config(
                            'pasax.access_log.auto_scan_since_hours',
                            24,
                        ),
                    );
                    $schedule
                        ->command(
                            sprintf(
                                'pasax:scan-logs --import --min-level=high --since="%d hours ago"',
                                $hours,
                            ),
                        )
                        ->dailyAt('03:00')
                        ->name('pasax-scan-access-logs');
                }
            } catch (\Throwable) {
                // Ignore schedule registration errors if Schedule is not bound
            }
        });
    }

    protected function registerGate(): void
    {
        if (! Gate::has('manage-pasax')) {
            Gate::define('manage-pasax', function ($user) {
                if (method_exists($user, 'isAdmin')) {
                    return (bool) $user->isAdmin();
                }

                if (
                    in_array($user->role ?? null, ['admin', 'superadmin'], true)
                ) {
                    return true;
                }

                if (isset($user->is_admin)) {
                    return (bool) $user->is_admin;
                }

                if (isset($user->role)) {
                    return in_array($user->role, ['admin', 'superadmin'], true);
                }

                return ! (bool) config('pasax.dashboard.admin_only', false);
            });
        }
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware(
            'pasax.admin',
            PasaxEnsureSecurityAdmin::class,
        );
        $router->aliasMiddleware('pasax.block', PasaxBlockIpAddress::class);
        $router->aliasMiddleware(
            'pasax.detect',
            PasaxDetectSecurityThreats::class,
        );
        $router->aliasMiddleware(
            'pasax.activity',
            PasaxTrackUserActivity::class,
        );
        $router->aliasMiddleware(
            'pasax.announce',
            PasaxInjectAnnouncementBanner::class,
        );
        $router->aliasMiddleware(
            'pasax.rotating',
            PasaxRotatingDashboard::class,
        );
        $router->aliasMiddleware(
            'pasax.share-slug',
            PasaxShareDashboardSlug::class,
        );

        if (config('pasax.auto_register_middleware', false)) {
            $this->app->booted(function (): void {
                $kernel = $this->app->make(KernelContract::class);

                if ($kernel instanceof HttpKernel) {
                    $kernel->pushMiddleware(PasaxBlockIpAddress::class);
                    $kernel->pushMiddleware(PasaxDetectSecurityThreats::class);
                    $kernel->pushMiddleware(
                        PasaxInjectAnnouncementBanner::class,
                    );
                }
            });
        }
    }

    /**
     * Daftarkan pendengar event command terminal untuk memeriksa versi terbaru secara non-blocking
     * saat menjalankan "php artisan serve" atau "composer run dev" (artisan dev).
     */
    protected function registerVersionCheck(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (! (bool) config('pasax.version_check.enabled', true)) {
            return;
        }

        Event::listen(CommandStarting::class, function (
            CommandStarting $event,
        ): void {
            if (! in_array($event->command, ['serve', 'dev'], true)) {
                return;
            }

            try {
                $versionChecker = $this->app->make(
                    PasaxVersionCheckService::class,
                );
                $versionChecker->notifyIfUpdateAvailable($event->output);
            } catch (\Throwable) {
                // Abaikan kesalahan agar tidak pernah mengganggu server dev
            }
        });
    }
}
