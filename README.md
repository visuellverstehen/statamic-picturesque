# Picturesque

A Statamic addon that provides a custom tag for building HTML-only responsive images. It utilizes Glide to resize and crop images and create sources within a `<picture>` element for different DPRs and screen sizes.

To learn more about responsive images and how to use them, [this article](https://ericportis.com/posts/2014/srcset-sizes/) by Eric Portis is highly recommended. Although it was published in 2014 the key takeaways are now at least as relevant as they were back then.

## How to Install

Run the following command from your project root:

```bash
composer require visuellverstehen/statamic-picturesque
```

## How to Use

The tag is available as `{{ picture }}` or `{{ picturesque }}` and supports different ways of using it:

### Non-breakpoint based image with `size` attribute

Creates a picture element using a single source with a resized/cropped image for all defined DPRs.

e. g. `{{ picture:img size="300x200" }}` or `{{ picture:img size="300|1.5:1" }}`

Takes width and (optionally) ratio through the `size` attribute, with values separated by a pipe. Results in: 

```HTML
<picture>
    <source type="image/webp" srcset="
        [img-url]?fm=webp&amp;fit=crop&amp;w=300&amp;h=200&amp;s=[…] 1x,
        [img-url]?fm=webp&amp;fit=crop&amp;w=600&amp;h=400&amp;s=[…] 2x
    ">
    <img src="[img-url]?w=300&amp;fit=crop&amp;s=[…]" loading="lazy">
</picture>
```

### Breakpoint-based image without sizes

Creates a picture element using sources for each supplied breakpoint, each with a resized/cropped image for all defined DPRs.

e. g. `{{ picture:img default="300|1.5:1" md="1024|1.6:1" lg="1280|2:1" }}`

Takes width and ratio through breakpoint-specific attributes, with values separated by a pipe. Results in: 

```HTML
<picture>
    <source type="image/webp" media="(min-width: 1024px)" srcset="
        [img-url]?fm=webp&amp;fit=crop&amp;w=1280&amp;h=640&amp;s=[…] 1x,
        [img-url]?fm=webp&amp;fit=crop&amp;w=2560&amp;h=1280&amp;s=[…] 2x
    ">
    <source type="image/webp" media="(min-width: 768px)" srcset="
        [img-url]?fm=webp&amp;fit=crop&amp;w=1024&amp;h=640&amp;s=[…] 1x,
        [img-url]?fm=webp&amp;fit=crop&amp;w=2048&amp;h=1280&amp;s=[…] 2x
    ">
    <source type="image/webp" media="(min-width: 0px)" srcset="
        [img-url]?fm=webp&amp;fit=crop&amp;w=300&amp;h=200&amp;s=[…] 1x,
        [img-url]?fm=webp&amp;fit=crop&amp;w=600&amp;h=400&amp;s=[…] 2x
    ">
    <img src="[img-url]?w=300&amp;fit=crop&amp;s=[…]" loading="lazy">
</picture>
```

### Breakpoint-based image with sizes

Creates a picture element using sources for each supplied breakpoint, each with a resized/cropped image according to the supplied sizes.

e. g. `{{ picture:img default="300|1.5:1|100vw" md="1024|1.6:1|80vw" lg="1280|2:1|960px" }}`

Takes width, ratio and sizes through breakpoint-specific attributes, with values separated by a pipe. Results in: 

```HTML
<picture>
    <source type="image/webp" media="(min-width: 1024px)" srcset="
        [img-url]?fm=webp&amp;fit=crop&amp;w=1280&amp;h=640&amp;s=[…] 1280w,
        [img-url]?fm=webp&amp;fit=crop&amp;w=1920&amp;h=960&amp;s=[…] 1920w,
        [img-url]?fm=webp&amp;fit=crop&amp;w=2560&amp;h=1280&amp;s=[…] 2560w
    " sizes="960px">
    <source type="image/webp" media="(min-width: 768px)" srcset="
        [img-url]?fm=webp&amp;fit=crop&amp;w=1024&amp;h=640&amp;s=[…] 1024w,
        [img-url]?fm=webp&amp;fit=crop&amp;w=1536&amp;h=960&amp;s=[…] 1536w,
        [img-url]?fm=webp&amp;fit=crop&amp;w=2048&amp;h=1280&amp;s=[…] 2048w
    " sizes="80vw">
    <source type="image/webp" media="(min-width: 0px)" srcset="
        [img-url]?fm=webp&amp;fit=crop&amp;w=300&amp;h=200&amp;s=[…] 300w,
        [img-url]?fm=webp&amp;fit=crop&amp;w=450&amp;h=300&amp;s=[…] 450w,
        [img-url]?fm=webp&amp;fit=crop&amp;w=600&amp;h=400&amp;s=[…] 600w
    " sizes="100vw">
    <img src="[img-url]?w=300&amp;fit=crop&amp;s=[…]" loading="lazy">
</picture>
```

### Additional information for all ways

#### Spaces in attributes  
Feel free to use spaces within the attributes, if preferred. This might make the amount of parameters more readable, especially in multiline use:  
```
{{ picture:img 
    default="300 | 1.5:1 | 100vw" 
    md="1024 | 1.6:1 | 80vw" 
    lg="1280 | 2:1 | 960px"
}}
```

#### Image source  
The image source can be supplied in different ways:  
`{{ picture:img size="300|1.5:1" }}`  
or  
`{{ picture :src="img" size="300|1.5:1" }}`   
with `img` being the field handle of your asset field. 

If you're using the first option, be careful with the JSON mode ([see below](#json-mode)).  

You can also supply an asset path:
`{{ picture src="/assets/my-image.jpg" size="300|1.5:1" }}`   

#### Cropping, ratio and size
If you want to keep the original image ratio (and don't crop at all), use `auto` instead of a ratio:  
`{{ picture:img size="300 | auto | 100vw" […] }}`

When not cropping _and_ not using the `sizes` attribute you can simply omit everything but the image width:  
`{{ picture:img size="300" }}`  

Instead of using a ratio to crop an image you can also supply a width and height:  
`{{ picture:img size="300x100" md="600x400" }}`  

If you want to use `width x height` as well as the `sizes` attribute, simply use `auto` as the second parameter:  
`{{ picture:img size="300x100 | auto | 100vw" md="600x400 | auto | 80vw" }}`

The tag supports ratios as `1.5:1` or `1.5/1`, so use whichever way you prefer.

#### Format/filetypes

You can optionally add one or more filetypes for image conversion:  
`{{ picture:img size="300x100" format="jpg, webp" }}`  

If no `format` (or `filetype`) parameter is found, the default settings will be used (webp), which can be adjusted in the config. Please note that only supported filetypes can be used for conversion and all other supplied formats will be ignored.

#### Image orientation

By default Picturesque assumes you're working with landscape images. If you have a portrait image, you can change this behaviour by adding the `orientation` (or short `ori`, for all you impatient folks) parameter:  
`{{ picture:img size="300 | 2:1" orientation="portrait" }}`

All calculations for ratio etc. then use the first number as the height and calculate the width from it. If you pass two sizes (`300x100`) while using portrait orientation, the first size is considered as height and the second one as width.

#### Breakpoints

The breakpoints for the media attributes can be configured ([see below](#configuration)) and use the [tailwindcss breakpoints](https://tailwindcss.com/docs/responsive-design) as default.

The default (so essentially the `0` breakpoint) can be defined through either the `size` or `default` parameter.

#### Glide Parameters

You can pass any Glide image manipulation parameters to the tag using the `glide:` prefix:

```antlers
{{ picture:img size="300x200" glide:blur="20" }}
{{ picture:img size="300x200" glide:quality="85" glide:brightness="75" }}
{{ picture:img size="300x200" glide:fit="contain" glide:bg="ffffff" }}
{{ picture:img size="300x200" glide:filt="greyscale" }}
```

All standard Glide parameters are supported, including:
- **Effects**: `blur`, `brightness`, `contrast`, `gamma`, `sharpen`
- **Filters**: `filt` (greyscale, sepia, etc.)
- **Quality**: `quality` or `q`
- **Fit modes**: `fit` (contain, max, fill, stretch, crop)
- **Background**: `bg` for background color when using fill modes

Please refer to the [Glide documentation](https://glide.thephpleague.com/2.0/api/quick-reference/) for all available parameters.

##### Important notes
- Glide parameters for width (`w`, `width`) and height (`h`, `height`) are ignored as they are managed by Picturesque's responsive sizing.
- The `fit` parameter defaults to `crop_focal` when not specified.
- Glide parameters are applied to all generated image variants (different sizes, breakpoints and formats).

#### JSON mode
If you don't want to get a precompiled HTML string but rather have all the data as JSON for passing it on (e. g. to a Vue component), you can tell the tag to do just that:  
`{{ picture:json :src="img" size="300|1.5:1" }}`   
or  
`{{ picture:img output="json" size="300|1.5:1" }}`   

Results in:
```JSON
{
    "sources": {
        "default": {
            "type": "image\/webp",
            "srcset": "[img-url]?fm=webp&fit=crop&w=300&h=200&s=[…] 1x,[img-url]?fm=webp&fit=crop&w=600&h=400&s=[…] 2x"
        }
    },
    "img": {
        "alt": "Alt text provided by img asset.",
        "class": "",
        "src": "[img-url]?w=300&fit=crop&s=[…]",
        "loading": "lazy"
    }
}
```

### Additional `img` element attributes

To pass on some additional data to the generated `img` element, the following attributes are available:

#### Alt text
The tag by default checks the source asset for an alt text. You can overwrite the alt text like this:  
`{{ picture:img size="300x200" alt="I wish everyone would care about alt texts." }}`   

#### CSS classes
To attach css classes to the img element, use this:  
`{{ picture:img size="300x200" class="w-full object-cover" }}`   

If you want to attach classes to the picture element, use the `wrapperClass` attribute:  
`{{ picture:img size="300x200" wrapperClass="foo bar" }}`   

#### Lazy-loading
You can disable lazy loading (which is activated by default) like this:
`{{ picture:img size="300x200" lazy="false" }}`   

A setting in the config ([see below](#configuration)) allows you to adjust the default behaviour.

### Using the base class

If you want to use the logic of the tag outside of an Antlers template you can simply use the `Picturesque` base class:

```php
use VV\Picturesque\Picturesque;

// ...

public function makePicture(string $imageUrl)
{   
    return (new Picturesque($imageUrl))
        ->default('300 | 1.5:1')
        ->breakpoint('md', '1024 | 1.6:1')
        ->breakpoint('lg', '1280 | 2:1 | 960px')
        ->format(['webp', 'jpg'])
        ->glideParams([
            'blur' => 10,
            'quality' => 85,
            'fit' => 'contain',
            'bg' => 'ffffff'
        ])
        ->alt('A huge billboard that says ›I wish everyone would care about alt texts.‹')
        ->class('w-full object-cover')
        ->lazy(true)
        ->generate() // you always have to call this!
        ->html(); // or ->json()
}
```

Please be aware that the image currently has to be a Statamic asset and must be findable through the Asset facade (`Statamic\Facades\Asset::find($url)`).

## Configuration

The addon provides several configuration options through it's `config/picturesque.php` file. Check out the descriptions in there. All settings have sensible default options, so in the best-cast-scenario you don't have to configure anything.

## More about us

- [www.visuellverstehen.de](https://visuellverstehen.de)

## License
The MIT license (MIT). Please take a look at the [license file](LICENSE.md) for more information.

