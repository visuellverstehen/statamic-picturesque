<?php

use VV\Picturesque\Picturesque;
use VV\Picturesque\PicturesqueException;

it('generates html with a single size', function () {
    $html = $this->picturesque('landscape')
        ->default('300|1.5:1')
        ->generate()
        ->html();

    expect($html)
        ->toContain('<picture>')
        ->toContain('</picture>')
        ->toContain('<source')
        ->toContain("type='image/webp'")
        ->toContain("width='300'")
        ->toContain("height='200'")
        ->toContain('<img src=')
        ->toContain("loading='lazy'");
});

it('generates html with breakpoints', function () {
    $html = $this->picturesque('landscape')
        ->default('300|1.5:1')
        ->breakpoint('md', '1024|1.6:1')
        ->breakpoint('lg', '1280|2:1')
        ->generate()
        ->html();

    expect($html)
        ->toContain('(min-width: 1024px)')
        ->toContain('(min-width: 768px)')
        ->toContain('1x')
        ->toContain('2x');

    // Default breakpoint source has no media attribute
    preg_match_all("/<source[^>]+>/", $html, $sources);
    $defaultSource = end($sources[0]);
    expect($defaultSource)->not->toContain('media=');
});

it('generates html with breakpoints and sizes', function () {
    $html = $this->picturesque('landscape')
        ->default('300|1.5:1|100vw')
        ->breakpoint('md', '1024|1.6:1|80vw')
        ->breakpoint('lg', '1280|2:1|960px')
        ->generate()
        ->html();

    expect($html)
        ->toContain("sizes='960px'")
        ->toContain("sizes='80vw'")
        ->toContain("sizes='100vw'")
        ->toContain('1280w')
        ->toContain('1024w')
        ->toContain('300w');
});

it('generates json output', function () {
    $json = $this->picturesque('landscape')
        ->default('300|1.5:1')
        ->generate()
        ->json();

    $data = json_decode($json, true);

    expect($data)->toBeArray()
        ->toHaveKeys(['sources', 'img'])
        ->and($data['sources'])->toHaveKey('default/webp')
        ->and($data['sources']['default/webp']['type'])->toBe('image/webp')
        ->and($data['sources']['default/webp']['width'])->toBe(300)
        ->and($data['sources']['default/webp']['height'])->toBe(200);
});

it('sets alt text', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->alt('My alt text')
        ->generate()
        ->html();

    expect($html)->toContain("alt='My alt text'");
});

it('sets css class on img element', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->class('w-full object-cover')
        ->generate()
        ->html();

    expect($html)->toContain("class='w-full object-cover'");
});

it('sets wrapper class on picture element', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->wrapperClass('wrapper-class')
        ->generate()
        ->html();

    expect($html)->toContain("<picture class='wrapper-class'>");
});

it('sets inline style on img element', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->style('aspect-ratio: 3/2')
        ->generate()
        ->html();

    expect($html)->toContain("style='aspect-ratio: 3/2'");
});

it('enables lazy loading by default', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->generate()
        ->html();

    expect($html)->toContain("loading='lazy'");
});

it('disables lazy loading', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->lazy(false)
        ->generate()
        ->html();

    expect($html)->not->toContain('loading=');
});

it('supports multiple formats', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->format(['jpg', 'webp'])
        ->generate()
        ->html();

    expect($html)
        ->toContain("type='image/jpg'")
        ->toContain("type='image/webp'");
});

it('supports format as string', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->format('png')
        ->generate()
        ->html();

    expect($html)->toContain("type='image/png'");
});

it('falls back to default format for unsupported filetypes', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->format('bmp')
        ->generate()
        ->html();

    // bmp is unsupported, should fall back to default (webp)
    expect($html)->toContain("type='image/webp'");
});

it('supports portrait orientation', function () {
    $html = $this->picturesque('landscape')
        ->default('300|2:1')
        ->orientation('portrait')
        ->generate()
        ->html();

    // Portrait: height=300, ratio 2:1 => width = 300 * (1/2) = 150
    expect($html)
        ->toContain("width='150'")
        ->toContain("height='300'");
});

it('ignores invalid orientation and uses landscape', function () {
    $html = $this->picturesque('landscape')
        ->default('300|2:1')
        ->orientation('invalid')
        ->generate()
        ->html();

    // Falls back to landscape: width=300, ratio 2:1 => height = 300 * (1/2) = 150
    expect($html)
        ->toContain("width='300'")
        ->toContain("height='150'");
});

it('supports auto ratio preserving original aspect ratio', function () {
    $html = $this->picturesque('landscape')
        ->default('300|auto')
        ->generate()
        ->html();

    // 1600x900 => height = 300 * (900/1600) = 168.75 => rounded to 169
    expect($html)
        ->toContain("width='300'")
        ->toContain("height='169'");
});

it('supports explicit width x height', function () {
    $html = $this->picturesque('landscape')
        ->default('400x300')
        ->generate()
        ->html();

    expect($html)
        ->toContain("width='400'")
        ->toContain("height='300'");
});

it('passes glide parameters to generated urls', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->glideParams(['blur' => 20])
        ->generate()
        ->html();

    expect($html)->toHaveGlideParam('blur=20');
});

it('maps quality glide param to q shorthand', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->glideParams(['quality' => 85])
        ->generate()
        ->html();

    // Glide uses 'q' shorthand in URLs
    expect($html)->toHaveGlideParam('q=85');
});

it('filters out width and height glide params', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->glideParams(['w' => 999, 'width' => 999, 'h' => 999, 'height' => 999, 'blur' => 5])
        ->generate()
        ->html();

    // blur should be present, but the custom w/h should not override Picturesque's sizing
    expect($html)
        ->toHaveGlideParam('blur=5')
        ->toContain("width='300'")
        ->toContain("height='200'")
        ->notToHaveGlideParam('w=999')
        ->notToHaveGlideParam('h=999');
});

it('supports the size alias method', function () {
    $html = $this->picturesque('landscape')
        ->size('300x200')
        ->generate()
        ->html();

    expect($html)
        ->toContain("width='300'")
        ->toContain("height='200'");
});

it('supports the css alias method', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->css('my-class')
        ->generate()
        ->html();

    expect($html)->toContain("class='my-class'");
});

it('throws an exception for an invalid asset', function () {
    new Picturesque('/nonexistent/path.jpg');
})->throws(PicturesqueException::class);

it('works with a square asset', function () {
    $html = $this->picturesque('square')
        ->default('400|auto')
        ->generate()
        ->html();

    // 800x800 => auto ratio = 1.0 => height = 400
    expect($html)
        ->toContain("width='400'")
        ->toContain("height='400'");
});

it('generates correct dpr srcsets for non-breakpoint images', function () {
    $html = $this->picturesque('landscape')
        ->default('300x200')
        ->generate()
        ->html();

    // Should have 1x and 2x descriptors (default DPR config)
    expect($html)
        ->toContain('1x')
        ->toContain('2x');
});

it('generates w descriptors when sizes are specified', function () {
    $html = $this->picturesque('landscape')
        ->default('300|1.5:1|100vw')
        ->generate()
        ->html();

    // With sizes, should produce width descriptors (e.g. 300w, 450w, 600w)
    expect($html)
        ->toContain('300w')
        ->toContain('450w')
        ->toContain('600w')
        ->not->toContain('1x');
});

it('can chain all options fluently', function () {
    $html = $this->picturesque('landscape')
        ->default('300|1.5:1|100vw')
        ->breakpoint('md', '768|1.6:1|80vw')
        ->format(['webp', 'jpg'])
        ->alt('Fluent test')
        ->class('img-class')
        ->wrapperClass('wrapper')
        ->style('object-fit: cover')
        ->lazy(true)
        ->glideParams(['blur' => 10])
        ->generate()
        ->html();

    expect($html)
        ->toContain("<picture class='wrapper'>")
        ->toContain("alt='Fluent test'")
        ->toContain("class='img-class'")
        ->toContain("style='object-fit: cover'")
        ->toContain("loading='lazy'")
        ->toContain("type='image/webp'")
        ->toContain("type='image/jpg'")
        ->toHaveGlideParam('blur=10')
        ->toContain("sizes='80vw'")
        ->toContain("sizes='100vw'");
});

it('returns data array via data method', function () {
    $data = $this->picturesque('landscape')
        ->default('300|1.5:1')
        ->generate()
        ->data();

    expect($data)->toBeArray()
        ->toHaveKeys(['sources', 'img'])
        ->and($data['sources'])->toHaveKey('default/webp')
        ->and($data['img'])->toHaveKey('src');
});

it('exposes the asset via getAsset', function () {
    $picture = $this->picturesque('landscape');

    expect($picture->getAsset())->toBeInstanceOf(\Statamic\Assets\Asset::class);
});

it('accepts an asset object directly', function () {
    $picture = new Picturesque($this->assets['landscape']);

    $html = $picture->default('300x200')->generate()->html();

    expect($html)
        ->toContain('<picture>')
        ->toContain("width='300'")
        ->toContain("height='200'");
});
