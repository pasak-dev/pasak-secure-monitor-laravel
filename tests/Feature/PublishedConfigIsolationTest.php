<?php

use Illuminate\Support\Facades\File;

test('a config/pasak.php published by an earlier test is left in the skeleton', function () {
    // Simulates the copy pasak:install publishes into the Testbench skeleton,
    // here as an outdated version without any instant block signature.
    File::put(config_path('pasak.php'), "<?php\n\nreturn ['instant_block' => ['signatures' => []]];\n");

    expect(File::exists(config_path('pasak.php')))->toBeTrue();
});

test('the next test still uses the package config/pasak.php', function () {
    expect(File::exists(config_path('pasak.php')))->toBeFalse()
        ->and(config('pasak.instant_block.signatures'))->not->toBe([]);
});
