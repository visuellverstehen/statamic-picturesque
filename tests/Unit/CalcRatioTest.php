<?php

it('computes auto ratio from the asset dimensions', function () {
    // 1600x900 => ratio() = 16/9, auto = 1 / (16/9) = 9/16
    $result = $this->picturesque('landscape')->calcRatio('auto');

    expect($result)->toEqualWithDelta(900 / 1600, 0.0001);
});

it('computes auto ratio for square asset', function () {
    $result = $this->picturesque('square')->calcRatio('auto');

    expect($result)->toEqualWithDelta(1.0, 0.0001);
});

it('computes ratio from colon notation', function () {
    $result = $this->picturesque('landscape')->calcRatio('16:9');

    expect($result)->toBe(9.0 / 16.0);
});

it('computes ratio from slash notation', function () {
    $result = $this->picturesque('landscape')->calcRatio('4/3');

    expect($result)->toBe(3.0 / 4.0);
});

it('computes ratio from plain float string', function () {
    $result = $this->picturesque('landscape')->calcRatio('0.75');

    expect($result)->toBe(0.75);
});
