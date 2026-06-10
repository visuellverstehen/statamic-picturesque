<?php

use VV\Picturesque\PicturesqueException;

it('parses explicit width x height in landscape mode', function () {
    $result = $this->picturesque('landscape')->parseSizeData('600x200');

    expect($result)->toBe([
        [
            'width' => '600',
            'height' => '200',
        ],
    ]);
});

it('parses explicit width x height in portrait mode', function () {
    $result = $this->picturesque('landscape')
        ->orientation('portrait')
        ->parseSizeData('600x200');

    expect($result)->toBe([
        [
            'width' => '200',
            'height' => '600',
        ],
    ]);
});

it('calculates height from width and ratio in landscape mode', function () {
    $result = $this->picturesque('landscape')->parseSizeData('600', 0.5);

    expect($result[0]['width'])->toBe('600')
        ->and($result[0]['height'])->toBe(300.0);
});

it('calculates width from height and ratio in portrait mode', function () {
    $result = $this->picturesque('landscape')
        ->orientation('portrait')
        ->parseSizeData('600', 0.5);

    expect($result[0]['width'])->toBe(300.0)
        ->and($result[0]['height'])->toBe('600');
});

it('uses auto ratio from asset when no ratio is provided', function () {
    // Asset is 1600x900, auto ratio = 900/1600 = 0.5625
    $result = $this->picturesque('landscape')->parseSizeData('800');

    expect($result[0]['width'])->toBe('800')
        ->and($result[0]['height'])->toBe(800.0 * (900 / 1600));
});

it('uses auto ratio for square asset', function () {
    // Asset is 800x800, auto ratio = 1.0
    $result = $this->picturesque('square')->parseSizeData('400');

    expect($result[0]['width'])->toBe('400')
        ->and($result[0]['height'])->toBe(400.0);
});

it('does not crash when ratio is null', function () {
    // Regression test for PR #28 — must not throw or produce 0
    $result = $this->picturesque('landscape')->parseSizeData('300');

    expect($result[0]['width'])->toBe('300')
        ->and($result[0]['height'])->toBeGreaterThan(0);
});

it('parses comma-separated widths into multiple sources', function () {
    $result = $this->picturesque('landscape')->parseSizeData('300,600', 0.5);

    expect($result)->toHaveCount(2)
        ->and($result[0]['width'])->toBe('300')
        ->and($result[0]['height'])->toBe(150.0)
        ->and($result[1]['width'])->toBe('600')
        ->and($result[1]['height'])->toBe(300.0);
});

it('parses mixed comma-separated sizes with explicit dimensions', function () {
    $result = $this->picturesque('landscape')->parseSizeData('300,600x200', 0.5);

    expect($result)->toHaveCount(2)
        ->and($result[0]['width'])->toBe('300')
        ->and($result[0]['height'])->toBe(150.0)
        ->and($result[1])->toBe([
            'width' => '600',
            'height' => '200',
        ]);
});

it('parses comma-separated sizes in portrait mode', function () {
    $result = $this->picturesque('landscape')
        ->orientation('portrait')
        ->parseSizeData('600x200,800x400');

    expect($result)->toBe([
        [
            'width' => '200',
            'height' => '600',
        ],
        [
            'width' => '400',
            'height' => '800',
        ],
    ]);
});

it('throws a helpful error for non-numeric width x height', function () {
    $this->picturesque('landscape')->parseSizeData('foox200');
})->throws(PicturesqueException::class, 'must be numeric');

it('throws a helpful error for non-numeric comma-separated values', function () {
    $this->picturesque('landscape')->parseSizeData('300,foo');
})->throws(PicturesqueException::class, 'must be numeric');
