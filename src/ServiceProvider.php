<?php

namespace VV\Picturesque;

use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use VV\Picturesque\Tags\Picture;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Picture::class,
    ];

    public function bootAddon()
    {
        parent::boot();

        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'picturesque');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('picturesque.php'),
            ], 'picturesque');
        }

        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', ['--tag' => 'picturesque']);
        });
    }
}
