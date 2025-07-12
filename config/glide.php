<?php

return [
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
];
