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
        parent::setUp();
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.editions.pro', true);
    }
}
