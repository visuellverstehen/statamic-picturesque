<?php

namespace Tests;

use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use ReflectionClass;
use Statamic\Assets\Asset;
use Statamic\Extend\Manifest;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blink;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;
use VV\Picturesque\Picturesque;
use VV\Picturesque\ServiceProvider;

/**
 * Base test case for Picturesque tests.
 *
 * Sets up a Statamic environment with GD-generated image fixtures
 * and provides a convenient `picturesque()` factory method.
 *
 * Note: We extend OrchestraTestCase directly instead of Statamic's
 * AddonTestCase because the latter uses addToAssertionCount(-1) which
 * is incompatible with PHPUnit 12 (where the method is final and
 * rejects negative values).
 */
abstract class TestCase extends OrchestraTestCase
{
    /**
     * Resolved asset instances, keyed by fixture name.
     */
    protected array $assets = [];

    /**
     * Image fixtures: name => [width, height].
     */
    protected static array $fixtures = [
        'landscape' => [1600, 900],
        'square' => [800, 800],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Storage::fake('test_assets');

        AssetContainer::make('test')
            ->disk('test_assets')
            ->save();

        // Generate all image files first
        foreach (static::$fixtures as $name => [$width, $height]) {
            $this->createImageFile($name, $width, $height);
        }

        // Flush the container contents cache so all files are visible
        Blink::forget('asset-listing-cache-test');

        // Now register assets with their meta
        $container = AssetContainer::findByHandle('test');
        foreach (static::$fixtures as $name => [$width, $height]) {
            $asset = (new Asset)
                ->container($container)
                ->path("{$name}.jpg");

            $asset->writeMeta($asset->generateMeta());

            $this->assets[$name] = $asset;
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Register the addon in Statamic's manifest
        $reflector = new ReflectionClass(ServiceProvider::class);
        $directory = dirname($reflector->getFileName());

        $json = json_decode($app['files']->get($directory . '/../composer.json'), true);
        $statamic = $json['extra']['statamic'] ?? [];

        $app->make(Manifest::class)->manifest = [
            $json['name'] => [
                'id' => $json['name'],
                'slug' => $statamic['slug'] ?? null,
                'version' => 'dev-main',
                'namespace' => 'VV\\Picturesque',
                'autoload' => 'src',
                'provider' => ServiceProvider::class,
            ],
        ];

        $app['config']->set('statamic.editions.pro', true);
        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.stache.watcher', false);

        // Point stache stores to a fixtures directory (prevents scanning real content)
        $fixturesDir = $directory . '/../tests/__fixtures__';
        $app['config']->set('statamic.stache.stores.taxonomies.directory', $fixturesDir . '/content/taxonomies');
        $app['config']->set('statamic.stache.stores.terms.directory', $fixturesDir . '/content/taxonomies');
        $app['config']->set('statamic.stache.stores.collections.directory', $fixturesDir . '/content/collections');
        $app['config']->set('statamic.stache.stores.entries.directory', $fixturesDir . '/content/collections');
        $app['config']->set('statamic.stache.stores.navigation.directory', $fixturesDir . '/content/navigation');
        $app['config']->set('statamic.stache.stores.globals.directory', $fixturesDir . '/content/globals');
        $app['config']->set('statamic.stache.stores.global-variables.directory', $fixturesDir . '/content/globals');
        $app['config']->set('statamic.stache.stores.asset-containers.directory', $fixturesDir . '/content/assets');
        $app['config']->set('statamic.stache.stores.nav-trees.directory', $fixturesDir . '/content/structures/navigation');
        $app['config']->set('statamic.stache.stores.collection-trees.directory', $fixturesDir . '/content/structures/collections');
        $app['config']->set('statamic.stache.stores.form-submissions.directory', $fixturesDir . '/content/submissions');
        $app['config']->set('statamic.stache.stores.users.directory', $fixturesDir . '/users');
    }

    /**
     * Create a minimal JPEG image file on the faked disk.
     */
    protected function createImageFile(string $name, int $width, int $height): void
    {
        $image = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($image, 200, 200, 200);
        imagefill($image, 0, 0, $color);

        ob_start();
        imagejpeg($image, null, 60);
        $jpeg = ob_get_clean();
        imagedestroy($image);

        Storage::disk('test_assets')->put("{$name}.jpg", $jpeg);
    }

    /**
     * Create a Picturesque instance for the given fixture name.
     */
    protected function picturesque(string $fixture = 'landscape'): Picturesque
    {
        return new Picturesque($this->assets[$fixture]);
    }
}
