<?php

use RalphJSmit\Laravel\Glide\GlideImageGenerator;

if (! function_exists('glide')) {
    function glide(): GlideImageGenerator
    {
        return app(GlideImageGenerator::class);
    }
}
