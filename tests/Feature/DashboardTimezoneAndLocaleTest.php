<?php

use Illuminate\Support\Carbon;
use Pasak\SecureMonitor\Support\PasakDashboardView;

test('dashboard view detects server timezone or respects config', function () {
    $tz = PasakDashboardView::timezone();
    expect($tz)->not->toBeEmpty();
    expect(in_array($tz, DateTimeZone::listIdentifiers(), true))->toBeTrue();

    config()->set('pasak.dashboard.timezone', 'America/New_York');
    expect(PasakDashboardView::timezone())->toBe('America/New_York');

    config()->set('pasak.dashboard.timezone', null);
    config()->set('app.timezone', 'Asia/Tokyo');
    expect(PasakDashboardView::timezone())->toBe('Asia/Tokyo');
});

test('formatDate converts to resolved timezone and localized month names', function () {
    config()->set('pasak.dashboard.timezone', 'Asia/Jakarta'); // UTC+7

    // 2026-10-15 03:00:00 UTC -> 2026-10-15 10:00:00 in Jakarta
    $utcDate = Carbon::createFromFormat('Y-m-d H:i:s', '2026-10-15 03:00:00', 'UTC');

    app()->setLocale('id');
    Carbon::setLocale('id');
    $formattedId = PasakDashboardView::formatDate($utcDate, 'd M Y H:i:s');
    expect($formattedId)->toContain('10:00:00');
    expect($formattedId)->toContain('Okt'); // Indonesian October abbreviation

    app()->setLocale('en');
    Carbon::setLocale('en');
    $formattedEn = PasakDashboardView::formatDate($utcDate, 'd M Y H:i:s');
    expect($formattedEn)->toContain('10:00:00');
    expect($formattedEn)->toContain('Oct'); // English October abbreviation
});

test('diffForHumans respects current application locale', function () {
    $date = now()->subMinutes(5);

    app()->setLocale('id');
    Carbon::setLocale('id');
    $idDiff = PasakDashboardView::diffForHumans($date);
    expect($idDiff)->toContain('lalu');

    app()->setLocale('en');
    Carbon::setLocale('en');
    $enDiff = PasakDashboardView::diffForHumans($date);
    expect($enDiff)->toContain('ago');
});
