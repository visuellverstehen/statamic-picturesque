<?php

namespace VV\Picturesque\Tags;

use Statamic\Facades\Asset;
use Statamic\Tags\Tags;
use VV\Picturesque\Picturesque;

class Picture extends Tags
{
    protected static $aliases = ['picturesque'];

    private $mode = 'html';

    /**
     * {{ picture src="[src]" }}.
     *
     * Where `src` is an asset, a path or url.
     */
    public function index(): string
    {
        if (! $asset = $this->params->get(['src', 'id', 'path'])) {
            return '';
        }

        $this->mode = $this->params->get('output') ?? 'html';

        return $this->output($asset);
    }

    /**
     * {{ picture:json src="[src]" }}.
     *
     * Where `src` is an asset, a path or url.
     */
    public function json(): string
    {
        if (! $asset = $this->params->get(['src', 'id', 'path'])) {
            return '';
        }

        $this->mode = 'json';

        return $this->output($asset);
    }

    /**
     * {{ picture:[field] }}.
     *
     * Where `field` is the variable containing the image ID.
     * Notice that this won't work if the field handle is `json`.
     */
    public function __call($method, $args): string
    {
        $tag = explode(':', $this->tag, 2)[1];

        if (! $asset = $this->context->value($tag)) {
            return '';
        }

        $this->mode = $this->params->get('output') ?? 'html';

        return $this->output($asset);
    }
    
    protected function handleAltText(Picturesque &$picture)
    {
        // if no param is set, the asset will be checked for an alt text
        if ($alt = $this->params->get('alt')) {
            $picture->alt($alt);
        }
    }
    
    protected function handleCssClasses(Picturesque &$picture)
    {
        if ($class = $this->params->get('class')) {
            $picture->css($class);
        }
    }
    
    protected function handleLazyLoading(Picturesque &$picture)
    {
        // if no param is set, config default is used
        if ($this->params->has('lazy')) {
            if ($this->params->get('lazy') == false) {
                $picture->lazy(false);
            }
            else {
                $picture->lazy(true);
            }
        }
    }
    
    protected function handleWrapperCssClasses(Picturesque &$picture)
    {
        if ($wrapperClass = $this->params->get('wrapperClass')) {
            $picture->wrapperClass($wrapperClass);
        }
    }

    protected function output($asset)
    {
        if (! is_a($asset, 'Statamic\Contracts\Assets\Asset')) {
            if (! $asset = Asset::find($asset)) {
                return '';
            }
        }

        $picture = new Picturesque($asset, $this->context);

        // format/filetypes
        if ($filetypes = $this->params->get(['format', 'filetype', 'filetypes'])) {
            $filetypes = str_replace(' ', '', $filetypes);

            if (str_contains($filetypes, ',')) {
                $filetypes = explode(',', $filetypes);
            } else if (str_contains($filetypes, '|')) {
                $filetypes = explode('|', $filetypes);
            }

            $picture->format($filetypes);
        }

        // orientation (landscape/portrait)
        if ($orientation = $this->params->get(['orientation', 'ori'])) {
            $picture->orientation($orientation);
        }

        // source tags
        if ($picture->isGlideSupportedFiletype()) {
            // breakpoint-based sources, e. g. with `md` attribute
            if ($this->params->get(array_keys(config('picturesque.breakpoints')))) {
                foreach (array_keys(config('picturesque.breakpoints')) as $breakpoint) {
                    if (! $param = $this->params->get($breakpoint)) {
                        continue;
                    }
                    $picture->breakpoint($breakpoint, $param);
                }
            }

            // non-breakpoint based image with `size` or `default` attribute
            if ($param = $this->params->get('size')) {
                $picture->default($param);
            }
        }

        $this->handleAltText($picture);
        $this->handleCssClasses($picture);
        $this->handleWrapperCssClasses($picture);
        $this->handleLazyLoading($picture);

        $picture->generate();

        if ($this->mode == 'json' || $this->mode == 'array' ) {
            return $picture->json();
        }

        return $picture->html();
    }
}
