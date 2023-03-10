<?php

namespace VV\Picturesque\Tags;

use Statamic\Tags\Tags;

class Picture extends Tags
{
    protected static $aliases = ['picturesque'];

    /**
     * The {{ picture }} tag.
     *
     * @return string|array
     */
    public function index()
    {
        return 'Picturesque!';
    }
}
