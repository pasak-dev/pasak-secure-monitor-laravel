@extends('pages.pasak.layout', ['heading' => __('User Sessions'), 'subheading' => __('Sesi login aktif, riwayat perangkat, dan IP terpercaya')])

@section('page_support_key', 'sessions')

@section('content')
@php
    $DV = \Pasak\SecureMonitor\Support\DashboardView::class;
@endphp
<div class="sec-grid-4">
    <div class="sec-stat-card">
        <div class="sec-stat-label">{{ __('Online now (5 min)') }}</div>
        <div class="sec-stat-value" style="color: var(--sec-success);">{{ number_format($onlineCount ?? 0) }}</div>
    </div>
    <div class="sec-stat-card">
        <div class="sec-stat-label">{{ __('Trusted IPs') }}</div>
        <div class="sec-stat-value">{{ number_format(count($trustedIps ?? [])) }}</div>
    </div>
</div>

<div class="sec-card">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
        <div>
            <h3 class="sec-card-title">{{ __('Trust my current IP') }}</h3>
            <p class="sec-card-subtitle" style="margin-top: 4px;">
                {{ __('Hanya IP dari sesi login Anda saat ini yang dapat disimpan sebagai IP terpercaya, bukan seluruh riwayat login.') }}
            </p>
        </div>
        <div>
            @if ($isCurrentIpTrusted ?? false)
                <span class="sec-badge sec-badge-success" style="font-size: 12px; padding: 6px 12px;">
                    🛡️ {{ __('IP Saat Ini Terverifikasi') }}
                </span>
            @else
                <span class="sec-badge sec-badge-low" style="font-size: 12px; padding: 6px 12px;">
                    {{ __('IP Belum Terdaftar') }}
                </span>
            @endif
        </div>
    </div>

    <div style="margin-top: 16px; padding: 14px 16px; background: var(--sec-bg-subtle); border: 1px solid var(--sec-border); border-radius: 8px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            <div>
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--sec-text-muted); font-weight: 600;">{{ __('Akun Aktif') }}</div>
                <div style="font-size: 13.5px; font-weight: 600; color: var(--sec-text); margin-top: 2px;">
                    {{ $currentUser?->name ?? session('pasak_dashboard_user', auth()->user()?->name ?? 'User') }}
                    <span style="font-size: 12px; font-weight: normal; color: var(--sec-text-muted);">({{ $currentUser?->email ?? auth()->user()?->email ?? '-' }})</span>
                </div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--sec-text-muted); font-weight: 600;">{{ __('IP Sesi Saat Ini') }}</div>
                <div class="sec-font-mono" style="font-size: 14px; font-weight: 700; color: var(--sec-primary); margin-top: 2px;">
                    {{ $currentIp ?? request()->ip() }}
                </div>
            </div>
            <div>
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--sec-text-muted); font-weight: 600;">{{ __('Status Proteksi Akun') }}</div>
                <div style="margin-top: 4px;">
                    @if ($isCurrentIpTrusted ?? false)
                        <span style="font-size: 12px; color: var(--sec-success); font-weight: 600;">✓ {{ __('Terpercaya (Bypass Quota/Karantina)') }}</span>
                    @else
                        <span style="font-size: 12px; color: var(--sec-text-muted);">{{ __('Akses Standar (Sesi Aktif)') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
            @if ($isCurrentIpTrusted ?? false)
                @if (!empty($currentTrusted?->id))
                    <form method="POST" action="{{ route('pasak.dashboard.trusted-ips.destroy', $currentTrusted->id) }}" data-sec-confirm="{{ __('Hapus IP saat ini (:ip) dari daftar IP terpercaya akun Anda?', ['ip' => $currentIp ?? request()->ip()]) }}" data-confirm-title="{{ __('Konfirmasi Hapus IP Terpercaya') }}" data-confirm-type="danger" data-confirm-btn="{{ __('Ya, Lepas Status') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="sec-btn sec-btn-secondary">{{ __('Hapus Dari IP Terpercaya') }}</button>
                    </form>
                @endif
            @else
                <button type="button" class="sec-btn sec-btn-primary" onclick="window.pasakOpenTrustIpModal();">🛡️ {{ __('Save my IP') }}</button>
            @endif
        </div>
    </div>
</div>

<div class="sec-card">
    <form method="GET" action="{{ route('pasak.dashboard.sessions') }}" class="sec-toolbar" style="margin-bottom: 0;">
        <div class="sec-toolbar-group" style="flex: 1;">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('Search IP, browser, user...') }}" class="sec-input" style="min-width: 200px; flex: 1;">
            <div style="display: inline-flex; align-items: center; gap: 6px;">
                <label for="filter_date_from" style="font-size: 12px; color: var(--sec-text-muted); font-weight: 500;">{{ __('From') }}:</label>
                <input id="filter_date_from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="sec-input" style="font-size: 13px;">
            </div>
            <div style="display: inline-flex; align-items: center; gap: 6px;">
                <label for="filter_date_to" style="font-size: 12px; color: var(--sec-text-muted); font-weight: 500;">{{ __('To') }}:</label>
                <input id="filter_date_to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="sec-input" style="font-size: 13px;">
            </div>
            <div style="display: inline-flex; align-items: center; gap: 6px;">
                <label for="filter_per_page" style="font-size: 12px; color: var(--sec-text-muted); font-weight: 500;">{{ __('Show') }}:</label>
                <select id="filter_per_page" name="per_page" class="sec-select" onchange="this.form.submit()" style="font-size: 13px;">
                    @foreach ([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected(($filters['per_page'] ?? 25) == $n)>{{ $n }} / {{ __('page') }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="sec-btn sec-btn-primary">{{ __('Filter') }}</button>
            <a href="{{ route('pasak.dashboard.sessions') }}" class="sec-btn sec-btn-secondary">{{ __('Reset') }}</a>
        </div>
    </form>
</div>

<div class="sec-card sec-card-flush">
    <div class="sec-card-header">
        <div>
            <h3 class="sec-card-title">{{ __('Login history') }} ({{ number_format($logins->total()) }})</h3>
            <p class="sec-card-subtitle" style="margin-top: 4px;">{{ __('Riwayat sesi pengguna yang mengakses dashboard Pasak.') }}</p>
        </div>
    </div>
    <div class="sec-table-wrap">
        <table class="sec-table">
            <thead>
                <tr>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('IP address') }}</th>
                    <th>{{ __('Browser') }}</th>
                    <th>{{ __('Last activity') }}</th>
                    <th style="text-align: right;">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse (($logins ?? []) as $login)
                    @php
                        $thisIp = $login->ip_address;
                        $isCurrentSession = ($login->session_id && $login->session_id === session()->getId()) || ($thisIp === ($currentIp ?? request()->ip()) && $login->user_id === ($currentUser?->id ?? auth()->id()));
                    @endphp
                    <tr @if ($isCurrentSession) style="background: rgba(37, 99, 235, 0.04);" @endif>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span>{{ $login->user?->name ?? $login->user?->email ?? '-' }}</span>
                                @if ($isCurrentSession)
                                    <span class="sec-badge sec-badge-success" style="font-size: 10px; padding: 2px 6px;">{{ __('Sesi Ini') }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="sec-font-mono" style="font-weight: 600;">{{ $login->ip_address }}</td>
                        <td style="font-size: 12px;">{{ $login->browser }} · {{ $login->operating_system }}</td>
                        <td style="font-size: 12px; color: var(--sec-text-muted);" title="{{ $login->last_activity_at ? $DV::diffForHumans($login->last_activity_at) : '' }}">{{ $login->last_activity_at ? $DV::formatDate($login->last_activity_at, 'd M Y H:i:s') : '—' }}</td>
                        <td style="text-align: right; white-space: nowrap;">
                            @if ($login->session_id)
                                <form method="POST" action="{{ route('pasak.dashboard.sessions.destroy', $login->session_id) }}" style="display: inline;" data-sec-confirm="{{ __('Paksa keluar (logout) sesi pengguna ini sekarang?') }}" data-confirm-title="{{ __('Konfirmasi Putus Sesi') }}" data-confirm-type="warning" data-confirm-btn="{{ __('Ya, Logout Sesi') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sec-btn sec-btn-secondary sec-btn-sm">{{ __('Logout') }}</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('pasak.dashboard.user-sessions.destroy', $login->id) }}" style="display: inline;" data-sec-confirm="{{ __('Hapus rekaman sesi login ini secara permanen?') }}" data-confirm-title="{{ __('Konfirmasi Hapus Riwayat') }}" data-confirm-type="danger" data-confirm-btn="{{ __('Ya, Hapus') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sec-btn sec-btn-ghost sec-btn-sm">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--sec-text-muted); padding: 32px;">{{ __('Belum ada riwayat sesi login dashboard yang tercatat.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="sec-datatable-footer">
        <div>
            @if ($logins->total() > 0)
                {{ __('Showing') }} <span style="font-weight: 600; color: var(--sec-text);">{{ $logins->firstItem() }}</span>
                {{ __('to') }} <span style="font-weight: 600; color: var(--sec-text);">{{ $logins->lastItem() }}</span>
                {{ __('of') }} <span style="font-weight: 600; color: var(--sec-text);">{{ number_format($logins->total()) }}</span> {{ __('entries') }}
            @else
                {{ __('Showing 0 entries') }}
            @endif
        </div>
        <div>
            {{ $logins->links('pages.pasak.pagination') }}
        </div>
    </div>
</div>

<div class="sec-card sec-card-flush">
    <div class="sec-card-header">
        <h3 class="sec-card-title">{{ __('Trusted IPs') }}</h3>
    </div>
    <div class="sec-table-wrap">
        <table class="sec-table">
            <thead>
                <tr>
                    <th>{{ __('User') }}</th>
                    <th>{{ __('IP address') }}</th>
                    <th>{{ __('Device') }}</th>
                    <th style="text-align: right;">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse (($trustedIps ?? []) as $ip)
                    <tr>
                        <td>{{ $ip->user?->name ?? $ip->user?->email ?? session('pasak_dashboard_user', 'Admin') }}</td>
                        <td class="sec-font-mono" style="font-weight: 600;">
                            {{ $ip->ip_address }}
                            @if ($ip->ip_address === ($currentIp ?? request()->ip()))
                                <span class="sec-badge sec-badge-success" style="font-size: 10px; padding: 2px 6px; margin-left: 4px;">{{ __('IP Anda Saat Ini') }}</span>
                            @endif
                        </td>
                        <td style="font-size: 12px;">{{ $ip->device_name ?? '-' }}</td>
                        <td style="text-align: right;">
                            <form method="POST" action="{{ route('pasak.dashboard.trusted-ips.destroy', $ip->id) }}" data-sec-confirm="{{ __('Hapus IP ini dari daftar IP terpercaya?') }}" data-confirm-title="{{ __('Konfirmasi Hapus IP Terpercaya') }}" data-confirm-type="danger" data-confirm-btn="{{ __('Ya, Hapus') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sec-btn sec-btn-ghost sec-btn-sm">🗑️</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--sec-text-muted); padding: 32px;">{{ __('No trusted IPs.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if (! ($isCurrentIpTrusted ?? false))
<!-- Modal Simpan IP Terpercaya Pasak (Hanya Tampil Saat IP Belum Tersimpan) -->
<div class="sec-modal-backdrop" id="pasakTrustIpModal" hidden style="position: fixed; inset: 0; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 20px;">
    <div class="sec-modal-box" role="dialog" aria-modal="true" aria-labelledby="pasakTrustIpTitle" style="background: var(--sec-card); border: 1px solid var(--sec-border); border-radius: var(--sec-radius); width: 100%; max-width: 500px; box-shadow: var(--sec-shadow-md); overflow: hidden; display: flex; flex-direction: column; animation: sec-modal-in 0.2s cubic-bezier(0.16, 1, 0.3, 1);">
        <!-- Modal Header -->
        <div class="sec-modal-header" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; background: var(--sec-card-hover); padding: 18px 20px; border-bottom: 1px solid var(--sec-border);">
            <div style="display: flex; gap: 12px; align-items: center;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(37, 99, 235, 0.12); border: 1px solid rgba(37, 99, 235, 0.25); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                    🛡️
                </div>
                <div>
                    <h3 class="sec-card-title" id="pasakTrustIpTitle" style="font-size: 16px; margin: 0 0 2px 0;">
                        {{ __('Simpan IP Sebagai IP Terpercaya') }}
                    </h3>
                    <p style="font-size: 12px; color: var(--sec-text-muted); margin: 0;">
                        {{ __('Konfirmasi pendaftaran IP sesi login aktif ke daftar proteksi') }}
                    </p>
                </div>
            </div>
            <button type="button" class="sec-btn sec-btn-ghost" onclick="window.pasakCloseTrustIpModal();" title="{{ __('Tutup') }}" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; color: var(--sec-text-muted);">
                ✕
            </button>
        </div>

        <!-- Modal Body -->
        <div class="sec-modal-body" style="padding: 20px; display: flex; flex-direction: column; gap: 14px; font-size: 13px; line-height: 1.5;">
            <p style="margin: 0; color: var(--sec-text); font-size: 13.5px;">
                {{ __('Anda berhasil masuk ke Pasak. Alamat IP sesi Anda saat ini belum terdaftar sebagai IP terpercaya akun.') }}
            </p>

            <div style="padding: 12px 16px; background: var(--sec-bg-subtle, var(--sec-card-hover)); border: 1px solid var(--sec-border); border-radius: var(--sec-radius-sm); display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: var(--sec-text-muted); font-size: 12px;">{{ __('Akun Aktif') }}:</span>
                    <span style="font-weight: 600; color: var(--sec-text);">
                        {{ $currentUser?->name ?? session('pasak_dashboard_user', auth()->user()?->name ?? 'Admin') }}
                        @if (!empty($currentUser?->email) || !empty(auth()->user()?->email))
                            <span style="font-size: 12px; font-weight: normal; color: var(--sec-text-muted);">({{ $currentUser?->email ?? auth()->user()?->email }})</span>
                        @endif
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: var(--sec-text-muted); font-size: 12px;">{{ __('IP Sesi Saat Ini') }}:</span>
                    <span class="sec-font-mono" style="font-weight: 700; color: var(--sec-primary); font-size: 14px;">
                        {{ $currentIp ?? request()->ip() }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                    <span style="color: var(--sec-text-muted); font-size: 12px;">{{ __('Status Perlindungan') }}:</span>
                    <span class="sec-badge sec-badge-low" style="font-size: 11px; padding: 2px 8px;">
                        {{ __('Belum Terdaftar') }}
                    </span>
                </div>
            </div>

            <div style="padding: 10px 14px; background: rgba(37, 99, 235, 0.06); border: 1px dashed rgba(37, 99, 235, 0.25); border-radius: var(--sec-radius-sm); font-size: 12px; color: var(--sec-text-muted); line-height: 1.45;">
                <div style="color: var(--sec-primary); font-weight: 600; margin-bottom: 2px;">
                    🛡️ {{ __('Keuntungan Menyimpan IP Terpercaya:') }}
                </div>
                <div>
                    {{ __('IP terpercaya dibebaskan dari pembatasan brute-force login throttle, bebas dari karantina otomatis, dan dikenali sebagai IP perangkat resmi Anda.') }}
                </div>
            </div>

            <p style="margin: 0; font-size: 11.5px; color: var(--sec-text-subtle);">
                ℹ️ {{ __('Hanya IP dari sesi login aktif Anda saat ini yang akan disimpan. Riwayat login IP lainnya tidak akan terpengaruh.') }}
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="sec-modal-footer" style="padding: 14px 20px; border-top: 1px solid var(--sec-border); background: var(--sec-card-hover); display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="sec-btn sec-btn-secondary" onclick="window.pasakCloseTrustIpModal();">
                {{ __('Nanti Saja') }}
            </button>
            <form method="POST" action="{{ route('pasak.dashboard.trusted-ips.store-my-ip') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="sec-btn sec-btn-primary">
                    🛡️ {{ __('Simpan IP Ini') }}
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        window.pasakOpenTrustIpModal = function() {
            var modal = document.getElementById('pasakTrustIpModal');
            if (modal) {
                modal.removeAttribute('hidden');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        };

        window.pasakCloseTrustIpModal = function() {
            var modal = document.getElementById('pasakTrustIpModal');
            if (modal) {
                modal.setAttribute('hidden', '');
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        };

        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Esc') {
                var modal = document.getElementById('pasakTrustIpModal');
                if (modal && !modal.hasAttribute('hidden') && modal.style.display !== 'none') {
                    window.pasakCloseTrustIpModal();
                }
            }
        });

        // Close on backdrop click
        var modalEl = document.getElementById('pasakTrustIpModal');
        if (modalEl) {
            modalEl.addEventListener('click', function(e) {
                if (e.target === modalEl) {
                    window.pasakCloseTrustIpModal();
                }
            });
        }

        // Tampilkan modal secara otomatis karena IP belum terdaftar
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                window.pasakOpenTrustIpModal();
            });
        } else {
            window.pasakOpenTrustIpModal();
        }
    })();
</script>
@endif
@endsection
