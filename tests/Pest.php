<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->in('Unit', 'Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/*
|--------------------------------------------------------------------------
| Custom Expectations
|--------------------------------------------------------------------------
*/

/**
 * Assert that a query parameter (e.g. "blur=20") appears in both
 * the <source> srcset URLs and the <img> src URL of a picture element.
 */
expect()->extend('toHaveGlideParam', function (string $param) {
    $html = $this->value;

    // Extract all srcset attribute values and decode HTML entities
    preg_match_all("/srcset='([^']+)'/", $html, $srcsetMatches);
    $srcsets = array_map('html_entity_decode', $srcsetMatches[1]);

    // Extract the img src attribute value and decode HTML entities
    preg_match("/<img[^>]+src='([^']+)'/", $html, $srcMatch);
    $src = isset($srcMatch[1]) ? html_entity_decode($srcMatch[1]) : null;

    expect($srcsets)->not->toBeEmpty();

    foreach ($srcsets as $srcset) {
        expect($srcset)->toContain($param);
    }

    expect($src)->not->toBeNull()->toContain($param);

    return $this;
});

/**
 * Assert that a query parameter does NOT appear in any
 * <source> srcset URL or <img> src URL of a picture element.
 */
expect()->extend('notToHaveGlideParam', function (string $param) {
    $html = $this->value;

    preg_match_all("/srcset='([^']+)'/", $html, $srcsetMatches);
    $srcsets = array_map('html_entity_decode', $srcsetMatches[1]);

    preg_match("/<img[^>]+src='([^']+)'/", $html, $srcMatch);
    $src = isset($srcMatch[1]) ? html_entity_decode($srcMatch[1]) : null;

    foreach ($srcsets as $srcset) {
        expect($srcset)->not->toContain($param);
    }

    if ($src) {
        expect($src)->not->toContain($param);
    }

    return $this;
});
