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
    ->in('Unit');

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

expect()->extend('toBeTrackedFor', function ($asset, $expectedCount = 1) {
    // $this->value is the entry
    $entry = $this->value;
    $assetPath = $asset->path();
    $assetContainer = 'assets';
    $entryId = $entry->id();

    // Check if a reference exists
    $exists = \Illuminate\Support\Facades\DB::table('asset_atlas')
        ->where('asset_path', $assetPath)
        ->where('asset_container', $assetContainer)
        ->where('item_id', $entryId)
        ->exists();

    expect($exists)->toBeTrue(
        "Expected asset reference for '{$assetPath}' in entry '{$entryId}' to exist in asset_atlas table, but it was not found."
    );

    // Verify the expected count
    $actualCount = \Illuminate\Support\Facades\DB::table('asset_atlas')
        ->where('asset_path', $assetPath)
        ->where('item_id', $entryId)
        ->count();

    expect($actualCount)->toBe($expectedCount,
        "Expected {$expectedCount} reference(s) for asset '{$assetPath}' in entry '{$entryId}', but found {$actualCount}."
    );

    return $this; // Enable chaining
});
