<?php

use Statamic\View\Antlers\Antlers;

beforeEach(function () {
    $this->antlers = app(Antlers::class);
});

/**
 * Parse an Antlers template and decode the JSON result.
 */
function parseJson(Antlers $antlers, string $template, array $data = []): array
{
    $json = (string) $antlers->parse($template, $data, true);

    return json_decode($json, true);
}

it('returns valid json when using picture:json tag', function () {
    $data = parseJson($this->antlers, '{{ picture:json :src="img" size="300|1.5:1" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($data)->toBeArray()
        ->toHaveKeys(['sources', 'img']);
});

it('returns valid json when using output=json param', function () {
    $data = parseJson($this->antlers, '{{ picture:img output="json" size="300|1.5:1" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($data)->toBeArray()
        ->toHaveKeys(['sources', 'img']);
});

it('contains source data with correct structure for single size', function () {
    $data = parseJson($this->antlers, '{{ picture:json :src="img" size="300|1.5:1" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($data['sources'])->toHaveKey('default/webp');

    $source = $data['sources']['default/webp'];
    expect($source)
        ->toHaveKeys(['type', 'srcset', 'width', 'height'])
        ->and($source['type'])->toBe('image/webp')
        ->and($source['width'])->toBe(300)
        ->and($source['height'])->toBe(200);
});

it('contains img data with expected attributes', function () {
    $data = parseJson($this->antlers, '{{ picture:json :src="img" size="300|1.5:1" alt="Test image" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($data['img'])
        ->toHaveKeys(['alt', 'src', 'loading', 'width', 'height'])
        ->and($data['img']['alt'])->toBe('Test image')
        ->and($data['img']['loading'])->toBe('lazy')
        ->and($data['img']['width'])->toBe(300)
        ->and($data['img']['height'])->toBe(200);
});

it('contains breakpoint sources in json mode', function () {
    $data = parseJson($this->antlers, '{{ picture:json :src="img" default="300|1.5:1" md="1024|1.6:1" lg="1280|2:1" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($data['sources'])
        ->toHaveKey('lg/webp')
        ->toHaveKey('md/webp');

    expect($data['sources']['lg/webp'])
        ->toHaveKey('media')
        ->and($data['sources']['lg/webp']['media'])->toBe('(min-width: 1024px)');
});

it('includes sizes attribute in json breakpoint sources', function () {
    $data = parseJson($this->antlers, '{{ picture:json :src="img" default="300|1.5:1|100vw" md="1024|1.6:1|80vw" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($data['sources']['md/webp']['sizes'])->toBe('80vw');
});

it('includes css class in json img data', function () {
    $data = parseJson($this->antlers, '{{ picture:json :src="img" size="300x200" class="my-class" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($data['img']['class'])->toBe('my-class');
});

it('omits loading attribute in json when lazy is false', function () {
    $data = parseJson($this->antlers, '{{ picture:json :src="img" size="300x200" lazy="false" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($data['img'])->not->toHaveKey('loading');
});

it('includes multiple format sources in json', function () {
    $data = parseJson($this->antlers, '{{ picture:json :src="img" size="300x200" format="jpg, webp" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($data['sources'])
        ->toHaveKey('default/jpg')
        ->toHaveKey('default/webp');
});

it('includes width and height in json img for explicit dimensions', function () {
    $data = parseJson($this->antlers, '{{ picture:json :src="img" size="400x250" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($data['img']['width'])->toBe(400)
        ->and($data['img']['height'])->toBe(250);
});
