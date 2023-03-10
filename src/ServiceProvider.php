<?php

namespace VV\Picturesque;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        \VV\Picturesque\Tags\Picture::class,
    ];

    public function bootAddon()
    {
        //
    }
}
