<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Breakpoints
    |--------------------------------------------------------------------------
    |
    | Define supported breakpoints for generating breakpoint-based sources.
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
    | Define in which multiples of the requested image size should sources be 
    | generated when using the `sizes` attribute.
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
    
];