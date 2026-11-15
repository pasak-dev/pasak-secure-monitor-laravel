<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Pasax\SecureMonitor\Models\PasaxLoginAttempt;
use Pasax\SecureMonitor\Models\PasaxSetting;
use Pasax\SecureMonitor\Services\PasaxServerSecurityService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Server audit page for the Blade monitoring dashboard.
 */
class PasaxServerController extends Controller
{
    use RespondsWithPasaxDashboard;

    public function index(
        Request $request,
        PasaxServerSecurityService $server,
    ): View {
        $force = $request->boolean('refresh');

        if ($force) {
            $server->forget();
        }

        return $this->dashboardResponse('pages.pasax.server', [
            'report' => $server->scan($force),
            'environment' => $server->environmentInfo(),
            'baseline' => $server->baselineSummary(),
            'integrity' => $server->integrityReport(),
            'suspicious' => $server->suspiciousFiles($force),
            'verified' => $server->verifiedFilesList(),
            'deleted' => $server->deletedFilesList(),
            'lockouts' => $server->activeLockouts(),
            'serverLocation' => PasaxSetting::getServerLocation(),
            'urls' => [
                'index' => route('pasax.dashboard.server'),
                'refresh' => route('pasax.dashboard.server', [
                    'refresh' => 1,
                ]),
                'baseline' => route('pasax.dashboard.server.baseline'),
                'baselineDestroy' => route(
                    'pasax.dashboard.server.baseline.destroy',
                ),
                'suspiciousDestroy' => route(
                    'pasax.dashboard.server.suspicious-files.destroy',
                ),
                'suspiciousDownload' => route(
                    'pasax.dashboard.server.suspicious-files.download',
                ),
                'suspiciousVerify' => route(
                    'pasax.dashboard.server.suspicious-files.verify',
                ),
                'suspiciousUnverify' => Route::has(
                    'pasax.dashboard.server.suspicious-files.unverify',
                )
                    ? route('pasax.dashboard.server.suspicious-files.unverify')
                    : '#',
                'lockoutDestroy' => route('pasax.dashboard.lockouts.destroy', [
                    'id' => '__ID__',
                ]),
                'updateLocation' => Route::has('pasax.dashboard.server.location')
                    ? route('pasax.dashboard.server.location')
                    : (Route::has('pasax.dashboard.server') ? route('pasax.dashboard.server') : '#'),
                'threatMap' => Route::has('pasax.dashboard.threat-map')
                    ? route('pasax.dashboard.threat-map')
                    : '#',
                'reportExport' => Route::has('pasax.dashboard.server.report.export')
                    ? route('pasax.dashboard.server.report.export')
                    : '#',
            ],
        ]);
    }

    public function createBaseline(
        PasaxServerSecurityService $server,
    ): RedirectResponse {
        $server->createBaseline(auth()->id());

        return back()->with('toast', [
            'type' => 'success',
            'message' => __('Integrity baseline created.'),
        ]);
    }

    public function deleteBaseline(
        PasaxServerSecurityService $server,
    ): RedirectResponse {
        $server->deleteBaseline();

        return back()->with('toast', [
            'type' => 'success',
            'message' => __('Integrity baseline deleted.'),
        ]);
    }

    public function deleteSuspiciousFile(
        Request $request,
        PasaxServerSecurityService $server,
    ): RedirectResponse {
        $validated = $request->validate([
            'file_path' => ['required', 'string'],
        ]);

        $result = $server->deleteSuspiciousFile(
            $validated['file_path'],
            auth()->id(),
        );

        return back()->with('toast', [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ]);
    }

    public function downloadSuspiciousFile(
        Request $request,
        PasaxServerSecurityService $server,
    ): BinaryFileResponse|RedirectResponse {
        $validated = $request->validate([
            'file_path' => ['required', 'string'],
        ]);

        $filePath = $validated['file_path'];

        if (! $server->isInspectable($filePath)) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => __(
                    'Berkas tidak ditemukan atau di luar cakupan pemindaian.',
                ),
            ]);
        }

        $fullPath = base_path($filePath);

        if (! is_file($fullPath) || ! is_readable($fullPath)) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => __('Berkas fisik tidak dapat dibaca di server.'),
            ]);
        }

        $downloadFilename =
            'pasax-sample-'.str_replace(['/', '\\'], '-', $filePath).'.txt';

        return response()->download($fullPath, $downloadFilename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition' => 'attachment; filename="'.$downloadFilename.'"',
        ]);
    }

    public function verifySuspiciousFile(
        Request $request,
        PasaxServerSecurityService $server,
    ): RedirectResponse {
        $validated = $request->validate([
            'file_path' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $server->verifySuspiciousFile(
            $validated['file_path'],
            auth()->id(),
            $validated['notes'] ?? null,
        );

        return back()->with('toast', [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'],
        ]);
    }

    public function unverifySuspiciousFile(
        Request $request,
        PasaxServerSecurityService $server,
    ): RedirectResponse {
        $validated = $request->validate([
            'file_path' => ['required', 'string'],
        ]);

        $result = $server->unverifySuspiciousFile($validated['file_path']);

        return back()->with('toast', [
            'type' => $result['success'] ?? false ? 'success' : 'error',
            'message' => $result['message'] ??
                __('Gagal membatalkan verifikasi berkas.'),
        ]);
    }

    public function releaseLockout(
        int|string $id,
        PasaxServerSecurityService $server,
    ): RedirectResponse {
        $attempt = PasaxLoginAttempt::query()->find($id);

        if ($attempt) {
            $server->releaseLockout($attempt);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => __('Lockout released.'),
        ]);
    }

    /**
     * Simpan pembaruan lokasi fisik server & koordinat Live Threat Map.
     */
    public function updateLocation(
        Request $request,
    ): RedirectResponse|JsonResponse {
        $validated = $request->validate([
            'server_location_name' => ['nullable', 'string', 'max:255'],
            'server_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'server_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'server_timezone' => ['nullable', 'string', 'max:64'],
        ]);

        $tz = $validated['server_timezone'] ?? null;
        if ($tz !== null && $tz !== '' && ! in_array($tz, \DateTimeZone::listIdentifiers(), true)) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Identifier zona waktu tidak valid.'),
                ], 422);
            }

            return back()->withErrors(['server_timezone' => __('Identifier zona waktu tidak valid.')]);
        }

        PasaxSetting::setServerLocation(
            $validated['server_location_name'] ?? null,
            isset($validated['server_latitude']) &&
            $validated['server_latitude'] !== '' &&
            $validated['server_latitude'] !== null
                ? (float) $validated['server_latitude']
                : null,
            isset($validated['server_longitude']) &&
            $validated['server_longitude'] !== '' &&
            $validated['server_longitude'] !== null
                ? (float) $validated['server_longitude']
                : null,
            $tz !== '' ? $tz : null,
        );

        $message = __(
            'Lokasi target server berhasil disimpan dan disinkronkan ke Live Threat Map.',
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => PasaxSetting::getServerLocation(),
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    /**
     * Bersihkan seluruh cache sistem dan optimasi Laravel (optimize:clear).
     */
    public function optimizeClear(
        Request $request,
    ): JsonResponse|RedirectResponse {
        try {
            Artisan::call('optimize:clear');
            $output = trim(Artisan::output());
        } catch (\Throwable $e) {
            $output = $e->getMessage();
        }

        $message = __(
            'Cache aplikasi, tampilan (views), routes, dan konfigurasi berhasil dibersihkan.',
        );

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

    /**
     * Unduh laporan hasil audit pentest & server hardening dalam format Markdown (.md).
     */
    public function exportReport(
        Request $request,
        PasaxServerSecurityService $server,
    ): \Illuminate\Http\Response {
        $startDate = (string) $request->input('start_date', $request->input('date', now()->toDateString()));
        $endDate = (string) $request->input('end_date', $request->input('date', $startDate));

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = now()->toDateString();
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = $startDate;
        }
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $force = $request->boolean('refresh');
        $markdown = $server->exportMarkdownReport($startDate, $endDate, auth()->id(), $force);
        $filename = ($startDate !== $endDate)
            ? "PASAX_Server_Security_Audit_Report_{$startDate}_sd_{$endDate}.md"
            : "PASAX_Server_Security_Audit_Report_{$startDate}.md";

        return response($markdown, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }


    /**
     * Halaman Laporan Audit Pentest & Server Hardening dengan pemilih tanggal/rentang tanggal dan preview langsung.
     */
    public function reportPage(
        Request $request,
        PasaxServerSecurityService $server,
    ): View {
        $startDate = (string) $request->input('start_date', $request->input('date', now()->toDateString()));
        $endDate = (string) $request->input('end_date', $request->input('date', $startDate));

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
            $startDate = now()->toDateString();
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $endDate = $startDate;
        }
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $isRange = $startDate !== $endDate;
        $force = $request->boolean('refresh');
        if ($force) {
            $server->forget();
        }

        $markdown = $server->exportMarkdownReport($startDate, $endDate, auth()->id(), $force);
        $htmlPreview = \Illuminate\Support\Str::markdown($markdown);
        $scan = $server->scan($force);

        try {
            $startCarbon = \Illuminate\Support\Carbon::parse($startDate);
            $endCarbon = \Illuminate\Support\Carbon::parse($endDate);
            $formattedDate = $isRange
                ? $startCarbon->translatedFormat('d M Y') . ' s/d ' . $endCarbon->translatedFormat('d M Y')
                : $startCarbon->translatedFormat('d F Y');
        } catch (\Throwable) {
            $formattedDate = $isRange ? "{$startDate} s/d {$endDate}" : $startDate;
        }

        $urlParams = ['start_date' => $startDate, 'end_date' => $endDate];

        return $this->dashboardResponse('pages.pasax.reports', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedDate' => $startDate,
            'formattedDate' => $formattedDate,
            'isRange' => $isRange,
            'markdown' => $markdown,
            'htmlPreview' => $htmlPreview,
            'summary' => $scan['summary'] ?? [],
            'serverLocation' => PasaxSetting::getServerLocation(),
            'urls' => [
                'index' => route('pasax.dashboard.reports', $urlParams),
                'refresh' => route('pasax.dashboard.reports', array_merge($urlParams, ['refresh' => 1])),
                'export' => route('pasax.dashboard.server.report.export', $urlParams),
                'server' => route('pasax.dashboard.server'),
            ],
        ]);
    }

}
