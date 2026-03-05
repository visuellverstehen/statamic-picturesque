<?php

it('parses explicit width x height in landscape mode', function () {
    $result = $this->picturesque('landscape')->parseSizeData('600x200');

    expect($result)->toBe([
        'width' => '600',
        'height' => '200',
    ]);
});

it('parses explicit width x height in portrait mode', function () {
    $result = $this->picturesque('landscape')
        ->orientation('portrait')
        ->parseSizeData('600x200');

    expect($result)->toBe([
        'width' => '200',
        'height' => '600',
    ]);
});

it('calculates height from width and ratio in landscape mode', function () {
    $result = $this->picturesque('landscape')->parseSizeData('600', 0.5);

    expect($result['width'])->toBe('600')
        ->and($result['height'])->toBe(300.0);
});

it('calculates width from height and ratio in portrait mode', function () {
    $result = $this->picturesque('landscape')
        ->orientation('portrait')
        ->parseSizeData('600', 0.5);

    expect($result['width'])->toBe(300.0)
        ->and($result['height'])->toBe('600');
});

it('uses auto ratio from asset when no ratio is provided', function () {
    // Asset is 1600x900, auto ratio = 900/1600 = 0.5625
    $result = $this->picturesque('landscape')->parseSizeData('800');

    expect($result['width'])->toBe('800')
        ->and($result['height'])->toBe(800.0 * (900 / 1600));
});

it('uses auto ratio for square asset', function () {
    // Asset is 800x800, auto ratio = 1.0
    $result = $this->picturesque('square')->parseSizeData('400');

    expect($result['width'])->toBe('400')
        ->and($result['height'])->toBe(400.0);
});

it('does not crash when ratio is null', function () {
    // Regression test for PR #28 — must not throw or produce 0
    $result = $this->picturesque('landscape')->parseSizeData('300');

    expect($result['width'])->toBe('300')
        ->and($result['height'])->toBeGreaterThan(0);
});
