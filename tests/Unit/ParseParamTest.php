<?php

it('parses a simple size string without ratio or sizes', function () {
    $result = $this->picturesque('landscape')->parseParam('600x400');

    expect($result['srcset'])->toBe(['width' => '600', 'height' => '400'])
        ->and($result['sizes'])->toBeNull();
});

it('parses a width-only string and uses auto ratio', function () {
    $result = $this->picturesque('landscape')->parseParam('800');

    expect($result['srcset']['width'])->toBe('800')
        ->and($result['srcset']['height'])->toBeGreaterThan(0)
        ->and($result['sizes'])->toBeNull();
});

it('parses width with explicit ratio', function () {
    $result = $this->picturesque('landscape')->parseParam('600|16:9');

    expect($result['srcset']['width'])->toBe('600')
        ->and($result['srcset']['height'])->toBe(337.5) // 600 * (9/16)
        ->and($result['sizes'])->toBeNull();
});

it('parses width with ratio and sizes attribute', function () {
    $result = $this->picturesque('landscape')->parseParam('600|1:1|100vw');

    expect($result['srcset']['width'])->toBe('600')
        ->and($result['srcset']['height'])->toBe(600.0)
        ->and($result['sizes'])->toBe('100vw');
});
