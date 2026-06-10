<?php

use VV\Picturesque\PicturesqueException;

it('parses a simple size string without ratio or sizes', function () {
    $result = $this->picturesque('landscape')->parseParam('600x400');

    expect($result['srcset'])->toBe([['width' => '600', 'height' => '400']])
        ->and($result['sizes'])->toBeNull();
});

it('parses a width-only string and uses auto ratio', function () {
    $result = $this->picturesque('landscape')->parseParam('800');

    expect($result['srcset'][0]['width'])->toBe('800')
        ->and($result['srcset'][0]['height'])->toBeGreaterThan(0)
        ->and($result['sizes'])->toBeNull();
});

it('parses width with explicit ratio', function () {
    $result = $this->picturesque('landscape')->parseParam('600|16:9');

    expect($result['srcset'][0]['width'])->toBe('600')
        ->and($result['srcset'][0]['height'])->toBe(337.5) // 600 * (9/16)
        ->and($result['sizes'])->toBeNull();
});

it('parses width with ratio and sizes attribute', function () {
    $result = $this->picturesque('landscape')->parseParam('600|1:1|100vw');

    expect($result['srcset'][0]['width'])->toBe('600')
        ->and($result['srcset'][0]['height'])->toBe(600.0)
        ->and($result['sizes'])->toBe('100vw');
});

it('parses comma-separated widths with ratio and sizes attribute', function () {
    $result = $this->picturesque('landscape')->parseParam('300,600 | 2:1 | 100vw');

    expect($result['srcset'])->toHaveCount(2)
        ->and($result['srcset'][0]['width'])->toBe('300')
        ->and($result['srcset'][0]['height'])->toBe(150.0)
        ->and($result['srcset'][1]['width'])->toBe('600')
        ->and($result['srcset'][1]['height'])->toBe(300.0)
        ->and($result['sizes'])->toBe('100vw');
});

it('throws when comma-separated widths are used without a sizes value', function () {
    $this->picturesque('landscape')->parseParam('300,600');
})->throws(PicturesqueException::class, 'require a sizes value');

it('throws when comma-separated widths have a ratio but no sizes value', function () {
    $this->picturesque('landscape')->parseParam('300,600 | 2:1');
})->throws(PicturesqueException::class, 'require a sizes value');
