<?php

use RalphJSmit\Laravel\Glide\GlideImageGenerator;

dd('X');
if ( ! function_exists('glide') ) {
    function glide(): GlideImageGenerator
    {
        return app(GlideImageGenerator::class);
    }
}
