<?php

namespace VV\Picturesque\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use League\Glide\Server;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Config;
use Statamic\Facades\Glide;
use Statamic\Facades\Site;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Str;
use VV\Picturesque\Jobs\GenerateImageVariants;

class ImageController extends Controller
{
    public function __construct(
        private Server $server,
        private Request $request
    ) {}

    public function generateByAsset($encoded)
    {
        $this->validateSignature();
        $decoded = base64_decode($encoded);

        // The string before the first slash is the container
        [$container, $path] = explode('/', $decoded, 2);

        throw_unless($container = AssetContainer::find($container), new NotFoundHttpException());

        throw_unless($asset = $container->asset($path), new NotFoundHttpException);

        return $this->doGenerateByAsset($asset);
    }

    private function doGenerateByAsset(AssetContract $asset)
    {
        $glideParameters = Glide::normalizeParameters($this->request->all());

        // If the size is already generated, return it.
        $cachedAsset = Glide::cacheStore()->get('asset::'.$asset->id().'::'.md5(json_encode($glideParameters)), null);
        if ($cachedAsset !== null) {
            return $this->server->getResponseFactory()->create($this->server->getCache(), $cachedAsset);
        }
        GenerateImageVariants::dispatch($asset, $glideParameters);
        return $this->server->getResponseFactory()->create($this->server->getCache(), (new ImageGenerator($this->server))->generateByAsset($asset, []));
    }

    public function generateByUrl($url)
    {
        $this->validateSignature();

        $url = base64_decode($url);

        $glideParameters = Glide::normalizeParameters($this->request->all());

        // If the size is already generated, return it.
        $cachedAsset = Glide::cacheStore()->get('url::'.$url.'::'.md5(json_encode($glideParameters)), null);
        if ($cachedAsset !== null) {
            return $this->server->getResponseFactory()->create($this->server->getCache(), $cachedAsset);
        }
        GenerateImageVariants::dispatch($url, $glideParameters, 'url');
        return $this->server->getResponseFactory()->create($this->server->getCache(), (new ImageGenerator($this->server))->generateByUrl($url, []));
    }

    public function generateByPath($path)
    {
        $this->validateSignature();

        if (Config::get('statamic.assets.auto_crop')) {
            if ($asset = Asset::find(Str::ensureLeft($path, '/'))) {
                return $this->doGenerateByAsset($asset);
            }
        }

        $glideParameters = Glide::normalizeParameters($this->request->all());

        // If the size is already generated, return it.
        $cachedAsset = Glide::cacheStore()->get('path::'.$path.'::'.md5(json_encode($glideParameters)), null);
        if ($cachedAsset !== null) {
            return $this->server->getResponseFactory()->create($this->server->getCache(), $cachedAsset);
        }
        GenerateImageVariants::dispatch($path, $glideParameters, 'path');
        return $this->server->getResponseFactory()->create($this->server->getCache(), (new ImageGenerator($this->server))->generateByPath($path, []));
    }

    private function validateSignature()
    {
        // If secure images aren't enabled, don't bother validating the signature.
        if (! Config::get('statamic.assets.image_manipulation.secure')) {
            return;
        }

        $path = Str::after($this->request->url(), Site::current()->absoluteUrl());

        try {
            SignatureFactory::create(Config::getAppKey())->validateRequest($path, $this->request->query->all());
        } catch (SignatureException $e) {
            abort(400, $e->getMessage());
        }
    }
}
