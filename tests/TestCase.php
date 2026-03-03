<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Statamic\Testing\AddonTestCase;
use VV\Picturesque\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    use RefreshDatabase;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function setUp(): void
    {
        // Statamic's AddonTestCase calls addToAssertionCount() with negative
        // values to offset Mockery expectations. PHPUnit 11+ asserts $count >= 0,
        // causing problems, so we temporarily disable PHP assertions.
        $previousAssertions = ini_get('zend.assertions');
        ini_set('zend.assertions', 0);

        parent::setUp();

        ini_set('zend.assertions', $previousAssertions);
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.editions.pro', true);
    }
}
