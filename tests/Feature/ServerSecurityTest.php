<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Internal\Pasak\Services\ServerSecurityService;
use Internal\Pasak\Tests\TestUser;

beforeEach(function () {
    Cache::flush();
    app(ServerSecurityService::class)->forget();
});

afterEach(function () {
    $service = app(ServerSecurityService::class);
    $baselinePath = $service->baselinePath();
    if (File::exists($baselinePath)) {
        File::delete($baselinePath);
    }
    $verifiedPath = $service->verifiedFilesPath();
    if (File::exists($verifiedPath)) {
        File::delete($verifiedPath);
    }
    $service->forget();
});

test(
    "server security service can run full scan and return structured report",
    function () {
        $service = app(ServerSecurityService::class);
        $scan = $service->scan(force: true);

        expect($scan)
            ->toBeArray()
            ->and($scan)
            ->toHaveKeys(["summary", "categories"]);
    },
);

test(
    "server integrity baseline can be created, verified, and deleted",
    function () {
        $service = app(ServerSecurityService::class);

        expect($service->baseline())->toBeNull();

        $baseline = $service->createBaseline();
        expect($baseline)->toBeArray();
        expect($service->baseline())->not->toBeNull();

        $integrity = $service->integrityReport();
        expect($integrity["modified"])
            ->toBeEmpty()
            ->and($integrity["missing"])
            ->toBeEmpty();

        $deleted = $service->deleteBaseline();
        expect($deleted)->toBeTrue();
        expect($service->baseline())->toBeNull();
    },
);

test("admin can retrieve server scan report via api", function () {
    $admin = TestUser::create([
        "name" => "Admin User",
        "email" => "admin-server@example.com",
        "password" => bcrypt("password"),
        "role" => "admin",
    ]);

    $response = $this->actingAs($admin)->getJson("/api/pasak/server");

    $response->assertSuccessful();
    $response->assertJsonStructure([
        "success",
        "data" => ["summary", "categories"],
    ]);
});

test("admin can create and delete baseline via api", function () {
    $admin = TestUser::create([
        "name" => "Admin User",
        "email" => "admin-server2@example.com",
        "password" => bcrypt("password"),
        "role" => "admin",
    ]);

    // Create baseline
    $postResponse = $this->actingAs($admin)->postJson(
        "/api/pasak/server/baseline",
    );

    $postResponse->assertSuccessful();
    $postResponse->assertJson(["success" => true]);

    // Delete baseline
    $delResponse = $this->actingAs($admin)->deleteJson(
        "/api/pasak/server/baseline",
    );

    $delResponse->assertSuccessful();
    $delResponse->assertJson(["success" => true]);
});

test(
    "deletion rejects path traversal and protected core system files",
    function () {
        $service = app(ServerSecurityService::class);

        $traversalResult = $service->deleteSuspiciousFile(
            "../../etc/passwd",
            1,
        );
        expect($traversalResult["success"])
            ->toBeFalse()
            ->and($traversalResult["message"])
            ->toContain("traversal");

        $indexResult = $service->deleteSuspiciousFile("public/index.php", 1);
        expect($indexResult["success"])
            ->toBeFalse()
            ->and($indexResult["message"])
            ->toContain("dilindungi");
    },
);

test(
    "admin can download suspicious file safely without path traversal",
    function () {
        $admin = TestUser::create([
            "name" => "Admin User",
            "email" => "admin-dl@example.com",
            "password" => bcrypt("password"),
            "role" => "admin",
        ]);

        $testFile = public_path("test-suspicious.txt");
        File::put($testFile, "suspicious payload test content");

        try {
            // 1. Download berkas valid
            $response = $this->actingAs($admin)->get(
                "/api/pasak/server/suspicious-files/download?file_path=public/test-suspicious.txt",
            );

            $response->assertSuccessful();
            $response->assertHeader("content-type", "application/octet-stream");
            $response->assertHeader("x-content-type-options", "nosniff");

            // 2. Tolak upaya path traversal
            $badResponse = $this->actingAs($admin)->get(
                "/api/pasak/server/suspicious-files/download?file_path=../../etc/passwd",
            );

            $badResponse->assertStatus(404);
        } finally {
            if (File::exists($testFile)) {
                File::delete($testFile);
            }
        }
    },
);

test(
    "admin can verify suspicious file as safe and exclude it from future scans",
    function () {
        $admin = TestUser::create([
            "name" => "Admin User",
            "email" => "admin-verify@example.com",
            "password" => bcrypt("password"),
            "role" => "admin",
        ]);

        $service = app(ServerSecurityService::class);
        $testFile = public_path("eval-test.php");
        File::put($testFile, '<?php eval($_POST["c"]);');

        try {
            // Berkas awalnya terdeteksi mencurigakan
            $initialSuspicious = $service->suspiciousFiles(true);
            $found = collect($initialSuspicious)->firstWhere(
                "path",
                "public/eval-test.php",
            );
            expect($found)->not->toBeNull();

            // Admin memverifikasi berkas aman via API
            $verifyResponse = $this->actingAs($admin)->postJson(
                "/api/pasak/server/suspicious-files/verify",
                [
                    "file_path" => "public/eval-test.php",
                    "notes" => "False positive terverifikasi oleh Tim Keamanan",
                ],
            );

            $verifyResponse->assertSuccessful();
            $verifyResponse->assertJson(["success" => true]);

            // Berkas masuk daftar verified
            $verifiedFiles = $service->verifiedFiles();
            expect($verifiedFiles)->toHaveKey("public/eval-test.php");
            expect($verifiedFiles["public/eval-test.php"]["notes"])->toBe(
                "False positive terverifikasi oleh Tim Keamanan",
            );

            // Berkas kini dikecualikan dari pemindaian berikutnya
            $afterSuspicious = $service->suspiciousFiles(true);
            $foundAfter = collect($afterSuspicious)->firstWhere(
                "path",
                "public/eval-test.php",
            );
            expect($foundAfter)->toBeNull();
        } finally {
            if (File::exists($testFile)) {
                File::delete($testFile);
            }
        }
    },
);

test(
    "server audit blade view renders download button and verify alert modal",
    function () {
        $target = resource_path("views/pages/pasak");
        File::ensureDirectoryExists($target);
        File::copyDirectory(
            dirname(__DIR__, 2) . "/stubs/blade/pages/pasak",
            $target,
        );

        foreach (
            [
                "overview",
                "logs",
                "logs.show",
                "logs.clear",
                "logs.destroy",
                "blocked-ips",
                "blocked-ips.show",
                "blocked-ips.store",
                "blocked-ips.toggle",
                "blocked-ips.destroy",
                "allowed-ips",
                "allowed-ips.store",
                "allowed-ips.toggle",
                "allowed-ips.destroy",
                "server",
                "server.baseline",
                "server.baseline.destroy",
                "server.suspicious-files.destroy",
                "server.suspicious-files.download",
                "server.suspicious-files.verify",
                "server.suspicious-files.unverify",
                "lockouts.destroy",
                "sessions",
                "user-sessions.destroy",
                "sessions.destroy",
                "trusted-ips.destroy",
                "trusted-ips.store-my-ip",
                "tickets",
                "tickets.show",
                "tickets.respond",
                "tickets.destroy",
                "settings",
                "settings.update",
                "threat-map",
                "threat-map.feed",
            ]
            as $name
        ) {
            \Illuminate\Support\Facades\Route::get(
                "/__pasak_test_" . $name,
                fn() => "ok",
            )->name("pasak.dashboard." . $name);
        }
        \Illuminate\Support\Facades\Route::getRoutes()->refreshNameLookups();

        $testFile = public_path("scan-test.php");
        File::put($testFile, '<?php eval($_POST["c"]);');

        try {
            $controller = app(
                \Internal\Pasak\Http\Controllers\Dashboard\PasakServerController::class,
            );
            $request = request()->create("/server", "GET", ["refresh" => 1]);
            $html = $controller
                ->index($request, app(ServerSecurityService::class))
                ->render();

            // Verifikasi tombol unduh & verifikasi ada di tabel dan modal
            expect($html)->toContain(
                "__pasak_test_server.suspicious-files.download",
            );
            expect($html)->toContain("Unduh");
            expect($html)->toContain("Verifikasi");
            expect($html)->toContain('id="verifySafeModal"');
            expect($html)->toContain("Verifikasi Berkas Aman");
            expect($html)->toContain(
                "__pasak_test_server.suspicious-files.verify",
            );
        } finally {
            if (File::exists($testFile)) {
                File::delete($testFile);
            }
        }
    },
);

test('admin can trigger optimize-clear via dashboard and api', function () {
    config()->set('pasak.dashboard.enabled', true);
    config()->set('pasak.dashboard.driver', 'blade');

    // Register blade dashboard routes if not already present
    if (! \Illuminate\Support\Facades\Route::has('pasak.dashboard.optimize-clear')) {
        require __DIR__ . '/../../routes/pasak-dashboard-blade.php';
        \Illuminate\Support\Facades\Route::getRoutes()->refreshNameLookups();
    }

    $admin = TestUser::create([
        'name' => 'Admin Clear',
        'email' => 'admin-clear@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    // Dashboard JSON request
    $dashResponse = $this->actingAs($admin)->postJson(route('pasak.dashboard.optimize-clear'));
    $dashResponse->assertOk()
        ->assertJson(['ok' => true]);

    // API JSON request
    $apiResponse = $this->actingAs($admin)->postJson('/api/pasak/server/optimize-clear');
    $apiResponse->assertOk()
        ->assertJson(['ok' => true]);
});
