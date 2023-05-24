<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Breakpoints
    |--------------------------------------------------------------------------
    |
    | Define supported breakpoints for generating breakpoint-based sources.
    | Note that the `default` key is mandatory.
    |
    */
    
    'breakpoints' => [
        'default' => 0,
        'sm' => 640,
        'md' => 768,
        'lg' => 1024,
        'xl' => 1280,
        '2xl' => 1536,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Size multipliers
    |--------------------------------------------------------------------------
    |
    | Define in which multiples of the requested image size sources should be 
    | generated. When using the default (1, 1.5, 2) and requesting an image in
    | 300px the tag generates sizes in 300px, 450px, 600px.
    |
    */
    
    'size_multipliers' => [1, 1.5, 2],
    
    /*
    |--------------------------------------------------------------------------
    | Device pixel ratios
    |--------------------------------------------------------------------------
    |
    | Define for which DPRs should sources be generated when *not* using the
    | `sizes` attribute. Use int or float values, e. g. for 2x => 2
    |
    */
    
    'dpr' => [1, 2],
    
    /*
    |--------------------------------------------------------------------------
    | Supported filetypes
    |--------------------------------------------------------------------------
    |
    | Define the supported filetypes for image processing.
    |
    */
    
    'supported_filetypes' => ['jpg', 'jpeg', 'png', 'webp'],
    
    /*
    |--------------------------------------------------------------------------
    | Default filetype
    |--------------------------------------------------------------------------
    |
    | Define the default filetype for generating sources.
    |
    */
    
    'default_filetype' => 'webp',
    
    /*
    |--------------------------------------------------------------------------
    | Minimum image width
    |--------------------------------------------------------------------------
    |
    | Defines the smallest width to use for image processing in px (int).
    |
    */
    
    'min_width' => 300,
    
    /*
    |--------------------------------------------------------------------------
    | Lazy loading default
    |--------------------------------------------------------------------------
    |
    | Should `loading="lazy"` be used by default or not?
    | You can always overwrite it by setting `lazy='false'` in the tag.
    |
    */
    
    'lazyloading' => true,
    
    /*
    |--------------------------------------------------------------------------
    | Full stop in alt tags
    |--------------------------------------------------------------------------
    |
    | Setting this to true ensures that the alt text always ends with a 
    | full stop.
    |
    */
    
    'alt_fullstop' => false,
    
];