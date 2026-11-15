<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

$uninstallDir = '';

beforeEach(function () use (&$uninstallDir) {
    $uninstallDir = sys_get_temp_dir().'/pasax_uninstall_test_'.uniqid();
    foreach (
        [
            'routes',
            'bootstrap',
            'app/Models',
            'config',
            'public',
            'resources/views/errors',
            'database/migrations',
        ] as $dir
    ) {
        File::makeDirectory($uninstallDir.'/'.$dir, 0755, true, true);
    }

    // Arahkan seluruh helper path Laravel ke direktori tiruan.
    $this->app->setBasePath($uninstallDir);

    // Simulasi aplikasi host yang sudah terinstall Pasax.
    File::put(
        $uninstallDir.'/routes/pasax.php',
        "<?php // pasax web routes\n",
    );
    File::put(
        $uninstallDir.'/routes/pasax-service.php',
        "<?php // pasax api routes\n",
    );
    File::put(
        $uninstallDir.'/routes/web.php',
        "<?php\n\nrequire __DIR__.'/pasax.php';\n",
    );
    File::put(
        $uninstallDir.'/routes/api.php',
        "<?php\n\nrequire __DIR__.'/pasax-service.php';\n",
    );
    File::put(
        $uninstallDir.'/bootstrap/app.php',
        <<<'PHP'
        <?php

        use Illuminate\Foundation\Application;
        use Illuminate\Foundation\Configuration\Middleware;

        return Application::configure(basePath: dirname(__DIR__))
            ->withMiddleware(function (Middleware $middleware): void {
                $middleware->append([
                    \Pasax\SecureMonitor\Http\Middleware\BlockIpAddress::class,
                    \Pasax\SecureMonitor\Http\Middleware\DetectSecurityThreats::class,
                ]);
            })
            ->create();
        PHP
        ,
    );
    File::put(
        $uninstallDir.'/app/Models/User.php',
        <<<'PHP'
        <?php

        namespace App\Models;

        use Illuminate\Foundation\Auth\User as Authenticatable;
        use Pasax\SecureMonitor\Concerns\HasSecurityRelations;

        class User extends Authenticatable
        {
            use HasSecurityRelations;
        }
        PHP
        ,
    );
    File::put(
        $uninstallDir.'/.env',
        "APP_NAME=Demo\n\n# Pasax\nPASAX_MONITOR_ENABLED=true\nPASAX_SUPPORT_EMAIL=a@b.test\n",
    );
    File::put(
        $uninstallDir.'/.env.example',
        "APP_NAME=Demo\n\n# Pasax\nPASAX_MONITOR_ENABLED=true\n",
    );
    File::put($uninstallDir.'/config/pasax.php', "<?php return [];\n");
    File::put($uninstallDir.'/public/.htaccess', 'Original htaccess');
    File::put(
        $uninstallDir.'/public/.htaccess.backup-20260101_000000',
        'Original htaccess',
    );
    File::put(
        $uninstallDir.'/resources/views/errors/blocked.blade.php',
        'blocked',
    );
    File::makeDirectory(
        $uninstallDir.'/resources/views/pages/pasax',
        0755,
        true,
        true,
    );
    File::makeDirectory(
        $uninstallDir.'/.agents/skills/pasax',
        0755,
        true,
        true,
    );
    File::put(
        $uninstallDir.'/.agents/skills/pasax/SKILL.md',
        'skill content',
    );
    File::put(
        $uninstallDir.'/AGENTS.md',
        "# Host Guidelines\n\n<!-- pasax-guidelines:start -->\nPasax Guidelines\n<!-- pasax-guidelines:end -->\n",
    );
});

afterEach(function () use (&$uninstallDir) {
    if (! empty($uninstallDir) && File::isDirectory($uninstallDir)) {
        File::deleteDirectory($uninstallDir);
    }
});

test(
    'pasax uninstall removes all traces without touching host files',
    function () use (&$uninstallDir) {
        foreach (
            [
                'pasax_blocked_ips',
                'pasax_logs',
                'pasax_login_attempts',
                'pasax_ip_unblock_requests',
                'pasax_user_logins',
                'pasax_trusted_ips',
                'pasax_allowed_ips',
                'pasax_settings',
            ] as $table
        ) {
            expect(Schema::hasTable($table))->toBeTrue();
        }

        $this->artisan('pasax:uninstall', [
            '--force' => true,
        ])->assertSuccessful();

        foreach (
            [
                'pasax_blocked_ips',
                'pasax_logs',
                'pasax_login_attempts',
                'pasax_ip_unblock_requests',
                'pasax_user_logins',
                'pasax_trusted_ips',
                'pasax_allowed_ips',
                'pasax_settings',
            ] as $table
        ) {
            expect(Schema::hasTable($table))->toBeFalse();
        }

        expect(File::exists($uninstallDir.'/routes/pasax.php'))
            ->toBeFalse()
            ->and(File::exists($uninstallDir.'/routes/pasax-service.php'))
            ->toBeFalse()
            ->and(File::get($uninstallDir.'/routes/web.php'))
            ->not->toContain('pasax.php')
            ->and(File::get($uninstallDir.'/routes/api.php'))
            ->not->toContain('pasax-service.php')
            ->and(File::get($uninstallDir.'/routes/web.php'))
            ->toContain('<?php')
            ->and(File::get($uninstallDir.'/bootstrap/app.php'))
            ->not->toContain('BlockIpAddress')
            ->and(File::get($uninstallDir.'/bootstrap/app.php'))
            ->toContain('withMiddleware')
            ->and(File::get($uninstallDir.'/bootstrap/app.php'))
            ->not->toContain('->append')
            ->and(File::get($uninstallDir.'/app/Models/User.php'))
            ->not->toContain('HasSecurityRelations')
            ->and(File::get($uninstallDir.'/app/Models/User.php'))
            ->toContain('class User')
            ->and(File::get($uninstallDir.'/.env'))
            ->not->toContain('PASAX_')
            ->and(File::get($uninstallDir.'/.env'))
            ->toContain('APP_NAME=Demo')
            ->and(File::exists($uninstallDir.'/config/pasax.php'))
            ->toBeFalse()
            ->and(File::get($uninstallDir.'/public/.htaccess'))
            ->toBe('Original htaccess')
            ->and(
                File::exists(
                    $uninstallDir.'/resources/views/errors/blocked.blade.php',
                ),
            )
            ->toBeFalse()
            ->and(File::isDirectory($uninstallDir.'/resources/views/errors'))
            ->toBeFalse()
            ->and(
                File::isDirectory(
                    $uninstallDir.'/resources/views/pages/pasax',
                ),
            )
            ->toBeFalse()
            ->and(File::isDirectory($uninstallDir.'/resources/views/pages'))
            ->toBeFalse()
            ->and(File::exists($uninstallDir.'/.agents/skills/pasax/SKILL.md'))
            ->toBeFalse()
            ->and(File::isDirectory($uninstallDir.'/.agents/skills/pasax'))
            ->toBeFalse()
            ->and(trim(File::get($uninstallDir.'/AGENTS.md')))
            ->toBe('# Host Guidelines')
            ->and(File::get($uninstallDir.'/AGENTS.md'))
            ->not->toContain('pasax-guidelines');
    },
);

test(
    'pasax uninstall removes middleware registered via use import and short class names',
    function () use (&$uninstallDir) {
        File::put(
            $uninstallDir.'/bootstrap/app.php',
            <<<'PHP'
            <?php

            use Illuminate\Foundation\Application;
            use Illuminate\Foundation\Configuration\Exceptions;
            use Illuminate\Foundation\Configuration\Middleware;
            use Illuminate\Http\Request;
            use Pasax\SecureMonitor\Http\Middleware\PasaxBlockIpAddress;
            use Pasax\SecureMonitor\Http\Middleware\PasaxDetectSecurityThreats;
            use Pasax\SecureMonitor\Http\Middleware\PasaxInjectAnnouncementBanner;

            return Application::configure(basePath: dirname(__DIR__))
                ->withRouting(
                    web: __DIR__.'/../routes/web.php',
                    commands: __DIR__.'/../routes/console.php',
                    health: '/up',
                )
                ->withMiddleware(function (Middleware $middleware): void {
                    $middleware->append([
                        PasaxBlockIpAddress::class,
                        PasaxDetectSecurityThreats::class,
                        PasaxInjectAnnouncementBanner::class,
                    ]);

                    $middleware->trustProxies(at: ['127.0.0.1', '::1']);
                })
                ->withExceptions(function (Exceptions $exceptions): void {
                    $exceptions->shouldRenderJsonWhen(
                        fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
                    );
                })->create();
            PHP
            ,
        );

        $this->artisan('pasax:uninstall', [
            '--force' => true,
        ])->assertSuccessful();

        $content = File::get($uninstallDir.'/bootstrap/app.php');

        expect($content)
            ->not->toContain('Pasax')
            ->and($content)
            ->toContain('withMiddleware')
            ->and($content)
            ->toContain('trustProxies')
            ->and($content)
            ->toContain('withExceptions')
            ->and($content)
            ->not->toContain('->append');

        exec(
            'php -l '.
                escapeshellarg($uninstallDir.'/bootstrap/app.php').
                ' 2>&1',
            $output,
            $code,
        );
        expect($code)->toBe(0);
    },
);

test(
    'pasax uninstall keeps host middleware whose name merely contains a pasax suffix',
    function () use (&$uninstallDir) {
        File::put(
            $uninstallDir.'/bootstrap/app.php',
            <<<'PHP'
            <?php

            return Application::configure(basePath: dirname(__DIR__))
                ->withMiddleware(function (Middleware $middleware): void {
                    $middleware->append([
                        SomeBlockIpAddress::class,
                        DetectSecurityThreatsV2::class,
                    ]);
                })
                ->create();
            PHP
            ,
        );

        $this->artisan('pasax:uninstall', [
            '--force' => true,
        ])->assertSuccessful();

        $content = File::get($uninstallDir.'/bootstrap/app.php');

        expect($content)
            ->toContain('SomeBlockIpAddress')
            ->and($content)
            ->toContain('DetectSecurityThreatsV2')
            ->and($content)
            ->toContain('->append');
    },
);

test(
    'pasax uninstall leaves valid bootstrap app when host has trust proxies plus pasax append',
    function () use (&$uninstallDir) {
        File::put(
            $uninstallDir.'/bootstrap/app.php',
            <<<'PHP'
            <?php

            use Illuminate\Foundation\Application;
            use Illuminate\Foundation\Configuration\Middleware;

            return Application::configure(basePath: dirname(__DIR__))
                ->withMiddleware(function (Middleware $middleware): void {
                    $middleware->trustProxies(at: ['127.0.0.1', '::1']);
                    $middleware->append([
                        \Pasax\SecureMonitor\Http\Middleware\BlockIpAddress::class,
                        \Pasax\SecureMonitor\Http\Middleware\DetectSecurityThreats::class,
                        \Pasax\SecureMonitor\Http\Middleware\InjectAnnouncementBanner::class,
                    ]);
                })
                ->create();
            PHP
            ,
        );

        $this->artisan('pasax:uninstall', [
            '--force' => true,
        ])->assertSuccessful();

        $content = File::get($uninstallDir.'/bootstrap/app.php');

        expect($content)
            ->not->toContain('Pasax')
            ->and($content)
            ->toContain('trustProxies');

        // Tidak boleh ada statement variabel gantung ("$middleware" tanpa method call).
        foreach (explode("\n", $content) as $line) {
            expect(trim($line))
                ->not->toBe('$middleware')
                ->and(trim($line))
                ->not->toBe('$middleware;');
        }

        exec(
            'php -l '.
                escapeshellarg($uninstallDir.'/bootstrap/app.php').
                ' 2>&1',
            $output,
            $code,
        );
        expect($code)->toBe(0);
    },
);

test(
    'pasax uninstall removes stub env comments and pasax backup files',
    function () use (&$uninstallDir) {
        $stub = file_get_contents(dirname(__DIR__, 2).'/stubs/env.stub');
        File::put($uninstallDir.'/.env', "APP_NAME=Demo\n\n".$stub."\n");
        File::put(
            $uninstallDir.'/routes/pasax.php.backup-20260101_000000',
            'backup',
        );
        File::put(
            $uninstallDir.'/public/.htaccess.backup-20260101_000000',
            'Original htaccess',
        );
        File::makeDirectory(
            $uninstallDir.
                '/resources/views/pages/pasax.backup-20260101_000000',
            0755,
            true,
            true,
        );

        $this->artisan('pasax:uninstall', [
            '--force' => true,
        ])->assertSuccessful();

        expect(File::get($uninstallDir.'/.env'))
            ->not->toContain('PASAX_')
            ->and(File::get($uninstallDir.'/.env'))
            ->not->toContain('# Pasax')
            ->and(File::get($uninstallDir.'/.env'))
            ->toContain('APP_NAME=Demo')
            ->and(
                File::exists(
                    $uninstallDir.'/routes/pasax.php.backup-20260101_000000',
                ),
            )
            ->toBeFalse()
            ->and(
                File::exists(
                    $uninstallDir.'/public/.htaccess.backup-20260101_000000',
                ),
            )
            ->toBeFalse()
            ->and(
                File::isDirectory(
                    $uninstallDir.
                        '/resources/views/pages/pasax.backup-20260101_000000',
                ),
            )
            ->toBeFalse();
    },
);

test('pasax uninstall respects keep options', function () {
    $this->artisan('pasax:uninstall', [
        '--force' => true,
        '--keep-tables' => true,
    ])->assertSuccessful();

    expect(Schema::hasTable('pasax_blocked_ips'))->toBeTrue();
});

test(
    'pasax uninstall removes its migration records so reinstall recreates tables',
    function () {
        expect(
            DB::table('migrations')
                ->where('migration', '2026_01_01_000001_create_pasax_tables')
                ->exists(),
        )->toBeTrue();

        $this->artisan('pasax:uninstall', [
            '--force' => true,
        ])->assertSuccessful();

        expect(
            DB::table('migrations')
                ->where('migration', 'like', '%pasax%')
                ->exists(),
        )
            ->toBeFalse()
            ->and(Schema::hasTable('pasax_blocked_ips'))
            ->toBeFalse();
    },
);

test(
    'pasax uninstall cleans pint-formatted route require with spaces and double quotes',
    function () use (&$uninstallDir) {
        File::put(
            $uninstallDir.'/routes/web.php',
            "<?php\n\nrequire __DIR__ . '/pasax.php';\n",
        );
        File::put(
            $uninstallDir.'/routes/api.php',
            "<?php\n\nrequire_once __DIR__ . \"/pasax-service.php\";\n",
        );
        File::put($uninstallDir.'/routes/pasax.php', "<?php\n");
        File::put($uninstallDir.'/routes/pasax-service.php', "<?php\n");

        $this->artisan('pasax:uninstall', [
            '--force' => true,
        ])->assertSuccessful();

        expect(File::get($uninstallDir.'/routes/web.php'))
            ->not->toContain('pasax.php')
            ->and(File::get($uninstallDir.'/routes/api.php'))
            ->not->toContain('pasax-service.php');
    },
);
