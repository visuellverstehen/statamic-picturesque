<?php

use Statamic\View\Antlers\Antlers;

beforeEach(function () {
    $this->antlers = app(Antlers::class);
});

/**
 * Helper to parse Antlers templates in trusted mode with asset context.
 */
function antlers(Antlers $antlers, string $template, array $data = []): string
{
    return (string) $antlers->parse($template, $data, true);
}

it('renders a picture element with the size attribute', function () {
    $html = antlers($this->antlers, '{{ picture :src="img" size="300x200" }}', [
        'img' => $this->assets['landscape'],
    ]);

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

it('renders a picture element using the field handle shorthand', function () {
    $html = antlers($this->antlers, '{{ picture:img size="300x200" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)
        ->toContain('<picture>')
        ->toContain('<source')
        ->toContain("width='300'")
        ->toContain("height='200'");
});

it('renders a picture element using src with asset id', function () {
    $html = antlers($this->antlers, '{{ picture src="test::landscape.jpg" size="300x200" }}', []);

    expect($html)
        ->toContain('<picture>')
        ->toContain("width='300'")
        ->toContain("height='200'");
});

it('renders breakpoint-based sources without sizes', function () {
    $html = antlers($this->antlers, '{{ picture:img default="300|1.5:1" md="1024|1.6:1" lg="1280|2:1" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)
        ->toContain('<picture>')
        ->toContain('(min-width: 1024px)')
        ->toContain('(min-width: 768px)')
        ->toContain('1x')
        ->toContain('2x');

    // Default breakpoint source has no media attribute
    preg_match_all('/<source[^>]+>/', $html, $sources);
    $defaultSource = end($sources[0]);
    expect($defaultSource)->not->toContain('media=');
});

it('renders breakpoint-based sources with sizes', function () {
    $html = antlers($this->antlers, '{{ picture:img default="300|1.5:1|100vw" md="1024|1.6:1|80vw" lg="1280|2:1|960px" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)
        ->toContain("sizes='960px'")
        ->toContain("sizes='80vw'")
        ->toContain("sizes='100vw'")
        ->toContain('1280w')
        ->toContain('1024w')
        ->toContain('300w');
});

it('applies a custom alt text', function () {
    $html = antlers($this->antlers, '{{ picture:img size="300x200" alt="Custom alt text" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)->toContain("alt='Custom alt text'");
});

it('applies css classes to the img element', function () {
    $html = antlers($this->antlers, '{{ picture:img size="300x200" class="w-full object-cover" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)->toContain("class='w-full object-cover'");
});

it('applies wrapper class to the picture element', function () {
    $html = antlers($this->antlers, '{{ picture:img size="300x200" wrapperClass="foo bar" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)->toContain("<picture class='foo bar'>");
});

it('disables lazy loading when lazy is false', function () {
    $html = antlers($this->antlers, '{{ picture:img size="300x200" lazy="false" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)->not->toContain('loading=');
});

it('supports auto ratio to keep original aspect ratio', function () {
    $html = antlers($this->antlers, '{{ picture:img size="300|auto" }}', [
        'img' => $this->assets['landscape'],
    ]);

    // 1600x900 => ratio 9/16 => height = 300 * 9/16 = 168.75 => rounded to 169
    expect($html)
        ->toContain("width='300'")
        ->toContain("height='169'");
});

it('supports multiple formats', function () {
    $html = antlers($this->antlers, '{{ picture:img size="300x200" format="jpg, webp" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)
        ->toContain("type='image/jpg'")
        ->toContain("type='image/webp'");
});

it('supports portrait orientation', function () {
    $html = antlers($this->antlers, '{{ picture:img size="300|2:1" orientation="portrait" }}', [
        'img' => $this->assets['landscape'],
    ]);

    // Portrait: first number is height (300), ratio 2:1 => width = 300 * (1/2) = 150
    expect($html)
        ->toContain("width='150'")
        ->toContain("height='300'");
});

it('supports short orientation param', function () {
    $html = antlers($this->antlers, '{{ picture:img size="300|2:1" ori="portrait" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)
        ->toContain("width='150'")
        ->toContain("height='300'");
});

it('passes glide parameters to generated urls', function () {
    $html = antlers($this->antlers, '{{ picture:img size="300x200" glide:blur="20" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)->toHaveGlideParam('blur=20');
});

it('returns empty string when asset field is missing', function () {
    $html = antlers($this->antlers, '{{ picture:img size="300x200" }}', []);

    expect($html)->toBe('');
});

it('returns empty string when src param asset is not found', function () {
    $html = antlers($this->antlers, '{{ picture src="/nonexistent.jpg" size="300x200" }}', []);

    expect($html)->toBe('');
});

it('supports the picturesque alias', function () {
    $html = antlers($this->antlers, '{{ picturesque:img size="300x200" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)
        ->toContain('<picture>')
        ->toContain("width='300'")
        ->toContain("height='200'");
});

it('supports spaces in attribute values', function () {
    $html = antlers($this->antlers, '{{ picture:img default="300 | 1.5:1 | 100vw" md="1024 | 1.6:1 | 80vw" }}', [
        'img' => $this->assets['landscape'],
    ]);

    expect($html)
        ->toContain("sizes='100vw'")
        ->toContain("sizes='80vw'")
        ->toContain('(min-width: 768px)');
});
