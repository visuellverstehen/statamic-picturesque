<?php

namespace VV\Picturesque;

use Illuminate\Support\Collection;
use Statamic\Assets\Asset;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Tags\Context;
use Statamic\Tags\Glide;
use Statamic\Tags\Parameters;

class Picturesque
{
    private $asset;
    private $breakpoints;
    private $data;
    private $glide;
    private $glideSource;
    private $supportedFiletype;
    private $options;
    
    public function __construct(Asset|string $asset)
    {
        try {
            if (! $asset instanceof Asset && ! $asset = AssetFacade::find($asset)) {
                throw new PicturesqueException('Invalid asset source.');
            }
            
            $this->asset = $asset;
        }
        catch (PicturesqueException $e) {
            return $e;
        }
        
        $this->breakpoints = collect();
        
        $this->data = [
            'sources' => [],
            'img' => [],
        ];
        
        $this->options = collect([
            'alt' => null,
            'class' => '',
            'lazy' => config('picturesque.lazyloading'),
        ]);
        
        $this->setupGlide();
    }
    
    public function alt(string $text): self
    {
        $this->options->put('alt', $text);
        
        return $this;
    }
    
    public function breakpoint(string $handle, string $params): self
    {
        $this->breakpoints()->put($handle, $params);
        
        return $this;
    }
    
    public function breakpoints(): Collection
    {
        return $this->breakpoints;
    }
    
    public function class(string $class): self
    {
        $this->options->put('class', $class);
        
        return $this;
    }
    
    public function css(string $css): self
    {
        return $this->class($css);
    }
    
    public function data(): array
    {
        return $this->data;
    }
    
    public function default(string $params): self
    {
        $this->breakpoints()->put('default', $params);
        
        return $this;
    }
    
    public function generate(): self
    {
        if ($this->isGlideSupportedFiletype()) {
            // breakpoint-based sources
            if ($this->breakpoints->hasAny(array_keys(config('picturesque.breakpoints')))) {
                $this->data['sources'] = $this->makeSourcesForBreakpoints();
            }
        
            // non-breakpoint based image with `size` attribute
            if ($size = (string) $this->breakpoints->get('default')) {
                $this->data['sources']['default'] = $this->makeSource($this->parseParam($size));
            }
        }
        
        $this->data['img'] = $this->makeImg();
        
        return $this;
    }
    
    public function html(): string
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
    
    public function isGlideSupportedFiletype(): bool|null
    {
        if (! $this->asset) {
            return null;
        }
        
        return $this->supportedFiletype;
    }
    
    public function json(): string
    {
        return json_encode($this->data);
    }
    
    public function lazy(bool $lazy): self
    {
        $this->options->put('lazy', $lazy);
        
        return $this;
    }
    
    public function size(string $params): self
    {
        return $this->default($params);
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
    
    private function evaluateFiletype()
    {
        if (! $this->asset->meta() || ! array_key_exists('mime_type', $this->asset->meta())) {
            return null;
        }
        $filetype = strtolower(explode('/', $this->asset->meta()['mime_type'])[1]);
        
        $this->supportedFiletype = in_array($filetype, config('picturesque.supported_filetypes'));
    }
    
    private function makeAlt(): string
    {
        if (($alt = $this->options->get('alt')) || 
            ($alt = $this->asset->data()->get('alt'))) {
            return strip_tags($alt);
        }
        
        return '';
    }
    
    private function makeClass(): string
    {
        if ($class = $this->options->get('class')) {
            return trim((string) $class);
        }
        
        return '';
    }
    
    /**
     * Generate a glide URL with the provided parameters.
     * The source asset is provided through the tag context.
     */
    private function makeGlideUrl($params)
    {
        $this->glide->params = Parameters::make(
            array_merge($params, $this->glideSource),
            $this->glide->context
        );
    
        return $this->glide->index();
    }
    
    private function makeImg()
    {
        $img = [
            'alt' => $this->makeAlt(),
        ];
    
        if (! $this->isGlideSupportedFiletype()) {
            $img['src'] = $this->sourceAsset->url();
        } else {
            $img['src'] = $this->makeGlideUrl(['width' => $this->smallestSrc(), 'fit' => 'crop_focal']);
        }
        
        // css class
        $css = $this->makeClass();
        if (! empty($css)) {
            $img['class'] = $css;
        }
        
        // lazy loading
        if ($this->options->get('lazy')) {
            $img['loading'] = 'lazy';
        }
    
        return $img;
    }
    
    private function makeSource(array|string $sourceData, string|null $format = null, string $breakpoint = null): array
    {
        if (! is_array($sourceData)) {
            $sourceData = $this->parseParam($sourceData);
        }
        
        $source = [];
        
        // type
        $format = $format ? $format : config('picturesque.default_filetype');
        $source['type'] = "image/{$format}";
        
        // media
        if ($breakpoint) {
            $source['media'] = "(min-width: ".config('picturesque.breakpoints')[$breakpoint]."px)";
        }
        
        // srcset
        $source['srcset'] = $this->makeSrcset($sourceData, $format);
        
        // sizes
        if ($sourceData['sizes']) {
            $source['sizes'] = $sourceData['sizes'];
        }
        
        return $source;
    }
    
    private function makeSourcesForBreakpoints(): array
    {
        return collect(config('picturesque.breakpoints'))
            ->sortDesc()
            ->map(function ($px, $breakpoint) {
                if ($this->breakpoints->get($breakpoint)) {
                    return $this->makeSource(
                        $this->breakpoints->get($breakpoint),
                        config('picturesque.default_filetype'),
                        (string) $breakpoint
                    );
                }
            })
            ->whereNotNull()
            ->all();
    }
    
    private function makeSrcset(array $sourceData, string $format, $glideOptions = []): string
    {
        $sources = [];
    
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
                $sources = array_merge($sources, collect(config('picturesque.size_multipliers'))
                    ->map(function ($multiplier) use ($source) {
                        return [
                            'width' => ((float) $source['width']) * $multiplier,
                            'height' => ((float) $source['height']) * $multiplier,
                        ];
                    })
                    ->unique()
                    ->transform(function ($sizes) use ($glideOptions) {
                        $options = array_merge($glideOptions, $sizes);
                        return "{$this->makeGlideUrl($options)} {$sizes['width']}w";
                    })
                    ->toArray()
                );
            }
            // with dpr
            else {
                $sources = array_merge($sources, collect(config('picturesque.dpr'))
                    ->mapWithKeys(function ($dpr) use ($source) {
                        return [$dpr => [
                            'width' => ((float) $source['width']) * $dpr,
                            'height' => ((float) $source['height']) * $dpr,
                        ]];
                    })
                    ->unique()
                    ->transform(function ($sizes, $dpr) use ($glideOptions) {
                        $options = array_merge($glideOptions, $sizes);
                        return "{$this->makeGlideUrl($options)} {$dpr}x";
                    })
                    ->toArray()
                );
            }
        }
    
        return implode(',', $sources);
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
    
    /**
     * Converts a size param string into a structured array.
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
     * Converts a string with srcset information into a structured array.
     * e.g. "300,600x200" -> [ ['width' => 300], ['width' => 600, 'height' => 200] ]
     * Supports a $ratio option to calc height (if no explicit height supplied).
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
    
    /**
     * Setup everything we need for Glide image generation.
     * This method simply utilises Statamics own `{{ glide }}` tag to generate image urls.
     */
    private function setupGlide()
    {
        $this->glideSource = ['src' => $this->asset];
        $this->evaluateFiletype();
        
        $context = new Context();
        
        $this->glide = new Glide();
        $this->glide->method = 'index';
        $this->glide->tag = 'glide:index';
        $this->glide->isPair = false;
        $this->glide->context = $context;
        $this->glide->params = Parameters::make(
            $this->glideSource,
            $context,
        );
    }
    
    private function smallestSrc(): int
    {
        return config('picturesque.min_width');
    }
    
    
}