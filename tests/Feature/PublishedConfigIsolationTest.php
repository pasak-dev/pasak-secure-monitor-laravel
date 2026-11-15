<?php

use Illuminate\Support\Facades\File;

test('a config/pasax.php published by an earlier test is left in the skeleton', function () {
    // Simulates the copy pasax:install publishes into the Testbench skeleton,
    // here as an outdated version without any instant block signature.
    File::put(config_path('pasax.php'), "<?php\n\nreturn ['instant_block' => ['signatures' => []]];\n");

    expect(File::exists(config_path('pasax.php')))->toBeTrue();
});

test('the next test still uses the package config/pasax.php', function () {
    expect(File::exists(config_path('pasax.php')))->toBeFalse()
        ->and(config('pasax.instant_block.signatures'))->not->toBe([]);
});
