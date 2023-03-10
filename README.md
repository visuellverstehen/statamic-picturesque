# Picturesque

A Statamic addon that provides a custom tag for building HTML-only responsive images. It utilizes Glide to resize and crop images and create sources within a `<picture>` element for different DPRs and screen sizes.

## How to Install

Run the following command from your project root:

```bash
composer require visuellverstehen/statamic-picturesque
```

## How to Use

The tag is available as `{{ picture }}` or `{{ picturesque }}` and supports three different use cases:

### Non-breakpoint based image with `size` attribute

Creates a picture element using a single source with a resized/cropped image for all defined DPRs.

e. g. `{{ picture:img size="300|1.5:1" }}`

Takes width and ratio through the `size` attribute, with values separated by a pipe. Results in: 

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

Takes width with and ratio through breakpoint-specific attributes, with values separated by a pipe. Results in: 

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

Takes width with, ratio and size through breakpoint-specific attributes, with values separated by a pipe. Results in: 

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

### Additional information for all three ways

#### Image source  
The image source can be supplied in different ways:  
`{{ picture:img size="300|1.5:1" }}`
or  
`{{ picture :src="img" size="300|1.5:1" }}`   
with `img` being the field handle of your asset field. 

If you're using the first option, be careful with the JSON mode ([see below](#json-mode)).  

You can also supply an asset path:
`{{ picture src="/assets/my-image.jpg" size="300|1.5:1" }}`   

#### Breakpoints

The breakpoints for the media attributes can be configured ([see below](#configuration)) and use the [tailwindcss breakpoints](https://tailwindcss.com/docs/responsive-design) as default.

#### Spaces in attributes  
Feel free to use spaces within the attributes to make it more readable:  
`{{ picture:img default="300 | 1.5:1 | 100vw" md="1024 | 1.6:1 | 80vw" lg="1280 | 2:1 | 960px" }}`

#### Ratios  
If you don't want to apply any cropping but want to keep the original image ratio, use `auto` instead:  
`{{ picture:img default="300 | auto | 100vw" […] }}`

The tag supports ratios as `1.5:1` or `1.5/1`, so use whichever way you prefer.

#### JSON mode
If you don't want to get a precompiled HTML string but rather have all the data as JSON for passing it on (e. g. to a Vue component), you can tell the tag to do just that:  
`{{ picture:json :src="img" size="300|1.5:1" }}`   
or  
`{{ picture:img output="json" size="300|1.5:1" }}`   

## Configuration

The addon provides several configuration options through it's `config/picturesque.php` file. Check out the descriptions in there. All settings have sensible default options, so in the best-cast-scenario you don't have to configure anything.

## More about us

- [www.visuellverstehen.de](https://visuellverstehen.de)

## License
The MIT license (MIT). Please take a look at the [license file](LICENSE.md) for more information.

