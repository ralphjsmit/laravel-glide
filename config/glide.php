<?php

return [
    /**
     * The default filesystem disk to read source images from.
     * Set to `null` to use the `public/` directory, or specify
     * a disk name like `public` or `s3` to read from
     * a configured filesystem disk instead.
     */
    'default_source_disk' => null,

    'route' => [
        /**
         * The domain that will be used to generate the Glide URLs.
         */
        'domain' => null,

        /**
         * Whether the generated Glide URLs should be signed.
         * For some browsers this differing query string
         * might prevent browser caching of the image,
         * but major browsers appear to handle fine.
         */
        'signed' => false,
    ],

    /**
     * By default, images receive a `max-width: {originalImageWidth}` rule,
     * so that CSS will not upscale them beyond their original width. This
     * can be overridden on a per-image basis using the `grow` function
     * parameter. However, it can also be configured globally here.
     */
    'grow' => false,

    /**
     * The default image scales to generate.
     */
    'scales' => [
        400,
        800,
        1200,
        1600,
        2000,
        2500,
        3000,
        3500,
        4000,
        5000,
        6000,
        7000,
        8000,
        9000,
        10000,
    ],

    /**
     * By default, upscaling is enabled up to 2x the original image size.
     * This is due to Retina / HiDPI-displays, where it can be crisper
     * to serve an upscaled interpolated Glide image than the original.
     */
    'upscale_enabled' => true,
];
