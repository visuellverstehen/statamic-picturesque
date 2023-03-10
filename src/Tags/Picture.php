<?php

namespace VV\Picturesque\Tags;

use Statamic\Facades\Asset;
use Statamic\Tags\Glide;
use Statamic\Tags\Parameters;
use Statamic\Tags\Tags;

class Picture extends Tags
{
    protected static $aliases = ['picturesque'];
    
    private $sourceAsset;
    private $glide;
    private $glideSource;
    private $isGlideSupportedFiletype;

    private $data = [
        'sources' => [],
        'img' => [],
    ];

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

    /**
     * Prepares all data required for generating a <picture> tag.
     */
    private function output($asset)
    {
        if (! $asset = Asset::find($asset)) {
            return '';
        }

        $this->setupGlide($asset);

        if ($this->isGlideSupportedFiletype) {
            /*
             * 3 supported cases:
             *
             * a)   non-breakpoint based image with `size` attribute
             *      e.g. {{ picture:img size="300|1.8:1" }}
             *      this can also be used as fallback for breakpoints (the `zero breakpoint`)
             * b)   breakpoint based image without sizes
             *      uses dpr-based image resizing (… 1x, … 2x)
             *      e.g. {{ picture:img default="800|1:1.5" md="1024|1,5:1" lg="1280|2:1" }}
             * c)   breakpoint based image with sizes
             *      uses specific size information for resizing
             *      e.g. {{ picture:img default="800|1:1.5|100vw" [etc.] }}
             */

            // breakpoint-based sources
            if ($this->params->get(array_keys(config('picturesque.breakpoints')))) {
                $this->data['sources'] = $this->generateBreakpointSourceTags();
            }

            // non-breakpoint based image with `size` attribute
            if ($size = $this->params->get('size')) {
                $this->data['sources']['default'] = $this->generateSourceTag($this->parseParam($size));
            }
        }

        $this->data['img'] = $this->generateImageTag();

        if ($this->mode == 'json' || $this->mode == 'array' ) {
            return $this->outputAsJson();
        }

        return $this->outputAsHtml();
    }

    /**
     * Generates a <picture> tag.
     */
    private function outputAsHtml()
    {
        $output = '<picture>';

        // sources
        foreach ($this->data['sources'] as $source) {
            $sourcetag = '<source';

            $sourcetag .= " type='{$source['type']}'";
            $sourcetag .= array_key_exists('media', $source) ? " media='{$source['media']}'" : '';
            $sourcetag .= " srcset='{$source['srcset']}'";
            $sourcetag .= array_key_exists('sizes', $source) ? " sizes='{$source['sizes']}'" : '';

            $sourcetag .= '/>';
            $output .= $sourcetag;
        }

        // img tag
        $img = $this->data['img'];
        $output .= "<img src='{$img['src']}'";
        $output .= empty($img['alt']) ? '' : " alt='{$img['alt']}'";
        $output .= empty($img['class']) ? '' : " class='{$img['class']}'";
        $output .= empty($img['loading']) ? '' : " loading='{$img['loading']}'";
        $output .= "/>";

        $output .= '</picture>';

        return $output;
    }

    /**
     * Returns the picture tag data as JSON.
     */
    private function outputAsJson()
    {
        return json_encode($this->data);
    }

    /**
     * Setup everything we need for Glide image generation.
     * Note that this tag utilises Statamics own Glide tag to generate image urls.
     */
    private function setupGlide($asset)
    {
        $this->sourceAsset = $asset;
        $this->glideSource = ['src' => $this->sourceAsset];
        $this->isGlideSupportedFiletype = $this->checkIfGlideSupportedFileType();

        $this->glide = new Glide();
        $this->glide->method = 'index';
        $this->glide->tag = 'glide:index';
        $this->glide->isPair = false;
        $this->glide->context = $this->context;
        $this->glide->params = Parameters::make(
            $this->glideSource,
            $this->context,
        );
    }

    /**
     * Generate a glide URL with the provided parameters.
     * The source asset is provided through the tag context.
     */
    private function generateGlideUrl($params)
    {
        $this->glide->params = Parameters::make(
            array_merge($params, $this->glideSource),
            $this->context
        );

        return $this->glide->index();
    }

    private function generateImageTag()
    {
        $img = [
            'alt' => $this->generateAltAttribute(),
            'class' => $this->generateClassAttribute(),
        ];

        if (! $this->isGlideSupportedFiletype) {
            $img['src'] = $this->sourceAsset->url();
        } else {
            $img['src'] = $this->generateGlideUrl(['width' => $this->getSmallestSrc(), 'fit' => 'crop_focal']);
        }

        // TODO add config for default value
        $img['loading'] = 'lazy';
        if ($this->params->has('lazy') && $this->params->get('lazy') == false) {
            $img['loading'] = null;
        }

        return $img;
    }

    private function generateSourceTag(array|string $sourceData, string $format = 'webp', string $breakpoint = null)
    {
        if (! is_array($sourceData)) {
            $sourceData = $this->parseParam($sourceData);
        }

        $source = [];

        // type
        $source['type'] = "image/{$format}";

        // media
        if ($breakpoint) {
            $source['media'] = "(min-width: ".config('picturesque.breakpoints')[$breakpoint]."px)";
        }

        // srcset
        $source['srcset'] = $this->generateSrcsetAttribute($sourceData, $format);

        // sizes
        if ($sourceData['sizes']) {
            $source['sizes'] = $sourceData['sizes'];
        }

        return $source;
    }

    private function generateBreakpointSourceTags(): array
    {
        return collect(config('picturesque.breakpoints'))
            ->sortDesc()
            ->map(function ($px, $breakpoint) {
                if ($this->params->get($breakpoint)) {
                    return $this->generateSourceTag(
                        $this->params->get($breakpoint),
                        'webp',
                        (string) $breakpoint
                    );
                }
            })
            ->whereNotNull()
            ->all();
    }

    private function generateSrcsetAttribute(array $sourceData, string $format, $glideOptions = []): string
    {
        $sources = [];
        $generatedWidths = [];

        if (! array_key_exists('format', $glideOptions)) {
            $glideOptions['format'] = $format;
        }

        // crop options
        if (! array_key_exists('fit', $glideOptions)) {
            $glideOptions['fit'] = 'crop_focal';
        }

        foreach ($sourceData['srcset'] as $source) {
            // with sizes
            if ($sourceData['sizes']) {
                foreach (config('picturesque.size_multipliers') as $multiplier) {
                    $w = ((float) $source['width']) * $multiplier;

                    // make sure to not generate a size twice
                    // TODO does not factor in height! -> unique on array when preparing?
                    if (in_array($w, $generatedWidths)) {
                        continue;
                    }
                    $generatedWidths[] = $w;

                    $glideOptions['width'] = $w;

                    // set image height, if available
                    if (array_key_exists('height', $source)) {
                        $glideOptions['height'] = ((float) $source['height']) * $multiplier;
                    }

                    $sources[]= "{$this->generateGlideUrl($glideOptions)} {$w}w";
                }
            }
            // with dpr
            else {
                foreach (config('picturesque.dpr') as $dpr) {
                    $w = ((float) $source['width']) * $dpr;

                    $glideOptions['width'] = $w;

                    // set image height, if available
                    if (array_key_exists('height', $source)) {
                        $glideOptions['height'] = ((float) $source['height']) * $dpr;
                    }

                    $sources[] = "{$this->generateGlideUrl($glideOptions)} {$dpr}x";
                }
            }
        }

        return implode(',', $sources);
    }

    private function generateClassAttribute(): string
    {
        if ($class = $this->params->get('class')) {
            return trim($class);
        }

        return '';
    }

    private function generateAltAttribute(): string
    {
        if ($alt = $this->params->get('alt')) {
            return $alt;
        }

        $alt = $this->sourceAsset->data()->get('alt');
        if (! empty($alt)) {
            return $alt;
        }

        return '';
    }

    private function calcRatio(string $ratio): float|string
    {
        if ($ratio == 'auto') {
            return $ratio;
        }

        if (strpos($ratio, ':')) {
            $ratio = explode(':', $ratio);
        } elseif (strpos($ratio, '/')) {
            $ratio = explode('/', $ratio);
        } else {
            return (float) $ratio;
        }

        $w = (float) $ratio[0];
        $h = (float) $ratio[1];

        return (float) $h / $w;
    }

    private function getSmallestSrc($srcset = null)
    {
        return '300'; // TODO look for either sizes or smallest breakpoint
    }

    /**
     * Converts the param string (`size` or `[breakpoint]`) into a structured array.
     */
    public function parseParam(string $data): array
    {
        $result = [
            'srcset' => null,
            'sizes' => null,
        ];

        if (! strpos($data, '|')) {
            $result['srcset'] = $this->parseSizeData(trim($data));

            return $result;
        }

        $data = explode('|', $data);

        $result['srcset'] = $this->parseSizeData(
            trim($data[0]),
            $this->calcRatio(trim($data[1]))
        );

        if (count($data) > 2) {
            $result['sizes'] = trim($data[2]);
        }

        return $result;
    }

    /**
     * Converts a string with srcset information into a structured array
     * e.g. "300,600x200" -> [ ['width' => 300], ['width' => 600, 'height' => 200] ]
     * supports a $ratio option to calc height (if no explicit height supplied).
     */
    private function parseSizeData(string $sizeData, float|string $ratio = 'auto'): array
    {
        if (strpos($sizeData, ',')) {
            $sizes = [];
            foreach (explode(',', $sizeData) as $size) {
                $sizes[] = $this->parseSingleSize($size, $ratio);
            }

            return $sizes;
        }

        return [$this->parseSingleSize($sizeData, $ratio)];
    }

    private function parseSingleSize(string $size, float|string $ratio = 'auto'): array
    {
        $size = trim($size);

        if (strpos($size, 'x')) {
            $size = explode('x', $size);

            return [
                'width' => trim($size[0]),
                'height' => trim($size[1]),
            ];
        }

        $result = [
            'width' => $size,
            'height' => $ratio,
        ];

        if (is_float($ratio)) {
            $result['height'] = ((float) $size) * $ratio;
        }

        return $result;
    }

    private function checkIfGlideSupportedFileType(): bool|null
    {
        if (! $this->sourceAsset->meta() || ! array_key_exists('mime_type', $this->sourceAsset->meta())) {
            return null;
        }
        $filetype = strtolower(explode('/', $this->sourceAsset->meta()['mime_type'])[1]);

        return in_array($filetype, config('picturesque.supported_filetypes'));
    }
}
