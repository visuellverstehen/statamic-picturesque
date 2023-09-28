<?php

use Illuminate\Support\Facades\Route;
use Statamic\Facades\Site;
use Statamic\Facades\URL;
use VV\Picturesque\Controllers\ImageController;
use VV\Picturesque\Picturesque;

Site::all()->map(function ($site) {
    return URL::makeRelative($site->url());
})->unique()->each(function ($sitePrefix) {
    Route::group(['prefix' => $sitePrefix.Picturesque::getQueueGenerationRoute()], function () {
        Route::get('/asset/{container}/{path?}', [ImageController::class, 'generateByAsset'])->where('path', '.*');
        Route::get('/http/{url}/{filename?}', [ImageController::class, 'generateByUrl']);
        Route::get('{path}', [ImageController::class, 'generateByPath'])->where('path', '.*');
    });
});
