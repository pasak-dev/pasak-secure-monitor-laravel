<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Internal\Pasak\Http\Controllers\Dashboard\PasakBlockedIpController;
use Internal\Pasak\Http\Controllers\Dashboard\PasakLogController;
use Internal\Pasak\Models\PasakBlockedIp;
use Internal\Pasak\Models\PasakLog;
use Internal\Pasak\Services\PasakService;

function publishBladeViewsForLogTrend(): void
{
    $target = resource_path("views/pages/pasak");
    File::ensureDirectoryExists($target);
    File::copyDirectory(
        dirname(__DIR__, 2) . "/stubs/blade/pages/pasak",
        $target,
    );
}

function registerLogTrendDummyRoutes(): void
{
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
            "server",
            "sessions",
            "tickets",
            "tickets.show",
            "tickets.respond",
            "tickets.destroy",
            "settings",
        ]
        as $name
    ) {
        Route::get("/__pasak_test_" . $name, fn() => "ok")->name(
            "pasak.dashboard." . $name,
        );
    }

    Route::getRoutes()->refreshNameLookups();
}

test(
    "logs dashboard page renders attack trend line chart with week, month, and year filters",
    function (): void {
        publishBladeViewsForLogTrend();
        registerLogTrendDummyRoutes();

        // Create a few logs across different dates
        PasakLog::query()->create([
            "ip_address" => "192.168.1.100",
            "event_type" => "sqli_attempt",
            "threat_level" => "critical",
            "path" => "/login",
            "rule_label" => "SQL Injection",
            "was_blocked" => true,
            "created_at" => now(),
        ]);

        PasakLog::query()->create([
            "ip_address" => "10.0.0.50",
            "event_type" => "xss_detected",
            "threat_level" => "high",
            "path" => "/search",
            "rule_label" => "Cross-Site Scripting",
            "was_blocked" => false,
            "created_at" => now()->subDays(2),
        ]);

        $controller = app(PasakLogController::class);

        // 1. Default (week)
        $requestWeek = request()->create("/logs", "GET", [
            "trend_range" => "week",
        ]);
        $viewWeek = $controller->index($requestWeek);
        $htmlWeek = $viewWeek->render();

        expect($viewWeek->getData())->toHaveKey("trend");
        expect($viewWeek->getData()["trendRange"])->toBe("week");
        expect($htmlWeek)->toContain("Attack trend");
        expect($htmlWeek)->toContain("polyline");
        expect($htmlWeek)->toContain("Minggu");
        expect($htmlWeek)->toContain("Bulan");
        expect($htmlWeek)->toContain("Tahun");

        // 2. Month range
        $requestMonth = request()->create("/logs", "GET", [
            "trend_range" => "month",
        ]);
        $viewMonth = $controller->index($requestMonth);
        expect($viewMonth->getData()["trendRange"])->toBe("month");
        $htmlMonth = $viewMonth->render();
        expect($htmlMonth)->toContain("Bulan (30 Hari)");

        // 3. Year range
        $requestYear = request()->create("/logs", "GET", [
            "trend_range" => "year",
        ]);
        $viewYear = $controller->index($requestYear);
        expect($viewYear->getData()["trendRange"])->toBe("year");
        $htmlYear = $viewYear->render();
        expect($htmlYear)->toContain("Tahun (12 Bulan)");
    },
);

test(
    "logs dashboard page renders block IP button and interactive quarantine modal",
    function (): void {
        publishBladeViewsForLogTrend();
        registerLogTrendDummyRoutes();

        PasakLog::query()->create([
            "ip_address" => "185.220.101.7",
            "event_type" => "exploit_probe",
            "threat_level" => "critical",
            "path" => "/security/unblock-tickets/1/respond",
            "rule_label" => "Suspicious Probe",
            "was_blocked" => true,
            "created_at" => now(),
        ]);

        $controller = app(PasakLogController::class);
        $request = request()->create("/logs", "GET");
        $html = $controller->index($request)->render();

        // Pastikan tombol blokir hadir di tabel
        expect($html)->toContain("openBlockModal");
        expect($html)->toContain("Blokir");

        // Pastikan modal dan seluruh elemen sesuai desain screenshot hadir
        expect($html)->toContain('id="blockIpModal"');
        expect($html)->toContain("Blokir Alamat IP");
        expect($html)->toContain(
            "Semua permintaan dari IP ini akan ditolak (HTTP 403) sampai blokir dibuka atau kedaluwarsa.",
        );
        expect($html)->toContain("Cakupan Pemblokiran");
        expect($html)->toContain("Hanya Perangkat");
        expect($html)->toContain("Seluruh IP Router");
        expect($html)->toContain("Durasi Blokir");
        expect($html)->toContain("Alasan");
        expect($html)->toContain("Catatan Internal");
        expect($html)->toContain("Batal");
        expect($html)->toContain("Blokir Seluruh IP");
    },
);

test(
    "admin can block IP from quarantine modal with IP or Device scope",
    function (): void {
        $controller = app(PasakBlockedIpController::class);
        $security = app(PasakService::class);

        // 1. Blokir seluruh IP router
        $reqIp = request()->create("/blocked-ips", "POST", [
            "ip_address" => "198.51.100.22",
            "block_scope" => "ip",
            "duration_hours" => 24,
            "reason" => "Tiket Pembukaan Blokir Ditolak",
            "notes" => "Investigasi manual tim SOC",
        ]);

        $responseIp = $controller->store($reqIp, $security);
        expect($responseIp->getStatusCode())->toBe(302);

        $blockIp = PasakBlockedIp::query()
            ->where("ip_address", "198.51.100.22")
            ->first();
        expect($blockIp)->not->toBeNull();
        expect($blockIp->block_scope)->toBe("ip");
        expect($blockIp->is_active)->toBeTrue();
        expect($blockIp->reason)->toBe("Tiket Pembukaan Blokir Ditolak");
        expect($blockIp->notes)->toBe("Investigasi manual tim SOC");
        expect($blockIp->expires_at)->not->toBeNull();

        // 2. Blokir perangkat spesifik
        $reqDev = request()->create("/blocked-ips", "POST", [
            "ip_address" => "203.0.113.88",
            "block_scope" => "device",
            "device_id" => "dev_abc123xyz",
            "local_ip" => "192.168.1.55",
            "duration_hours" => 12,
            "reason" => "Perangkat terinfeksi malware",
        ]);

        $responseDev = $controller->store($reqDev, $security);
        expect($responseDev->getStatusCode())->toBe(302);

        $blockDev = PasakBlockedIp::query()
            ->where("ip_address", "203.0.113.88")
            ->first();
        expect($blockDev)->not->toBeNull();
        expect($blockDev->block_scope)->toBe("device");
        expect($blockDev->device_id)->toBe("dev_abc123xyz");
        expect($blockDev->local_ip)->toBe("192.168.1.55");
        expect($blockDev->is_active)->toBeTrue();
    },
);
