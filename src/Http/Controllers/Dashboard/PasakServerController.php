<?php

namespace Internal\Pasak\Http\Controllers\Dashboard;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Routing\Controller;
use Internal\Pasak\Models\PasakLoginAttempt;
use Internal\Pasak\Services\PasakServerSecurityService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Server audit page for the Blade monitoring dashboard.
 */
class PasakServerController extends Controller
{
    use RespondsWithPasakDashboard;

    public function index(
        Request $request,
        PasakServerSecurityService $server,
    ): View {
        $force = $request->boolean("refresh");

        if ($force) {
            $server->forget();
        }

        return $this->dashboardResponse("pages.pasak.server", [
            "report" => $server->scan($force),
            "environment" => $server->environmentInfo(),
            "baseline" => $server->baselineSummary(),
            "integrity" => $server->integrityReport(),
            "suspicious" => $server->suspiciousFiles($force),
            "verified" => $server->verifiedFilesList(),
            "deleted" => $server->deletedFilesList(),
            "lockouts" => $server->activeLockouts(),
            "urls" => [
                "index" => route("pasak.dashboard.server"),
                "refresh" => route("pasak.dashboard.server", [
                    "refresh" => 1,
                ]),
                "baseline" => route("pasak.dashboard.server.baseline"),
                "baselineDestroy" => route(
                    "pasak.dashboard.server.baseline.destroy",
                ),
                "suspiciousDestroy" => route(
                    "pasak.dashboard.server.suspicious-files.destroy",
                ),
                "suspiciousDownload" => route(
                    "pasak.dashboard.server.suspicious-files.download",
                ),
                "suspiciousVerify" => route(
                    "pasak.dashboard.server.suspicious-files.verify",
                ),
                "suspiciousUnverify" => \Illuminate\Support\Facades\Route::has("pasak.dashboard.server.suspicious-files.unverify")
                    ? route("pasak.dashboard.server.suspicious-files.unverify")
                    : "#",
                "lockoutDestroy" => route("pasak.dashboard.lockouts.destroy", [
                    "id" => "__ID__",
                ]),
            ],
        ]);
    }

    public function createBaseline(
        PasakServerSecurityService $server,
    ): RedirectResponse {
        $server->createBaseline(auth()->id());

        return back()->with("toast", [
            "type" => "success",
            "message" => __("Integrity baseline created."),
        ]);
    }

    public function deleteBaseline(
        PasakServerSecurityService $server,
    ): RedirectResponse {
        $server->deleteBaseline();

        return back()->with("toast", [
            "type" => "success",
            "message" => __("Integrity baseline deleted."),
        ]);
    }

    public function deleteSuspiciousFile(
        Request $request,
        PasakServerSecurityService $server,
    ): RedirectResponse {
        $validated = $request->validate([
            "file_path" => ["required", "string"],
        ]);

        $result = $server->deleteSuspiciousFile(
            $validated["file_path"],
            auth()->id(),
            $request->ip(),
        );

        return back()->with("toast", [
            "type" => $result["success"] ?? false ? "success" : "error",
            "message" => $result["message"] ?? __("Unable to delete the file."),
        ]);
    }

    public function downloadSuspiciousFile(
        Request $request,
        PasakServerSecurityService $server,
    ): BinaryFileResponse|RedirectResponse {
        $filePath = (string) $request->query("file_path", "");
        $fileInfo = $server->getSuspiciousFileForDownload($filePath);

        if (!$fileInfo) {
            return back()->with("toast", [
                "type" => "error",
                "message" => __(
                    "Berkas tidak ditemukan atau jalur tidak valid.",
                ),
            ]);
        }

        return response()->download(
            $fileInfo["absolute_path"],
            $fileInfo["filename"],
            [
                "Content-Type" => "application/octet-stream",
                "X-Content-Type-Options" => "nosniff",
            ],
        );
    }

    public function verifySuspiciousFile(
        Request $request,
        PasakServerSecurityService $server,
    ): RedirectResponse {
        $validated = $request->validate([
            "file_path" => ["required", "string"],
            "notes" => ["nullable", "string", "max:500"],
        ]);

        $result = $server->verifySuspiciousFile(
            $validated["file_path"],
            auth()->id(),
            $validated["notes"] ?? null,
            $request->ip(),
        );

        return back()->with("toast", [
            "type" => $result["success"] ?? false ? "success" : "error",
            "message" =>
                $result["message"] ?? __("Gagal memverifikasi berkas."),
        ]);
    }

    public function unverifySuspiciousFile(
        Request $request,
        PasakServerSecurityService $server,
    ): RedirectResponse {
        $validated = $request->validate([
            "file_path" => ["required", "string"],
        ]);

        $result = $server->unverifySuspiciousFile($validated["file_path"]);

        return back()->with("toast", [
            "type" => $result["success"] ?? false ? "success" : "error",
            "message" =>
                $result["message"] ?? __("Gagal membatalkan verifikasi berkas."),
        ]);
    }

    public function releaseLockout(
        int|string $id,
        PasakServerSecurityService $server,
    ): RedirectResponse {
        $attempt = PasakLoginAttempt::query()->find($id);

        if ($attempt) {
            $server->releaseLockout($attempt);
        }

        return back()->with("toast", [
            "type" => "success",
            "message" => __("Lockout released."),
        ]);
    }

    /**
     * Bersihkan seluruh cache sistem dan optimasi Laravel (optimize:clear).
     */
    public function optimizeClear(Request $request): JsonResponse|RedirectResponse
    {
        try {
            Artisan::call('optimize:clear');
            $output = trim(Artisan::output());
        } catch (\Throwable $e) {
            $output = $e->getMessage();
        }

        $message = __('Cache aplikasi, tampilan (views), routes, dan konfigurasi berhasil dibersihkan.');

        if ($request->expectsJson() || $request->ajax()) {
            session()->flash('toast', [
                'type' => 'success',
                'message' => $message,
            ]);

            return response()->json([
                'ok' => true,
                'message' => $message,
                'output' => $output,
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => $message,
        ]);
    }
}
