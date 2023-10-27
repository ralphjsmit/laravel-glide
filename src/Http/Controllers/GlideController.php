<?php

namespace RalphJSmit\Laravel\Glide\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Responses\LaravelResponseFactory;
use League\Glide\ServerFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GlideController
{
    public function __invoke(Request $request, Application $application, Filesystem $filesystem, string $source): StreamedResponse
    {
        $server = ServerFactory::create([
            'response' => new LaravelResponseFactory($request),
            'source' => glide()->getSourcePath(),
            'cache' => glide()->getCachePath(),
            'base_url' => '',
        ]);

        $width = $request->integer('width');

        try {
            return $server->getImageResponse($source, [
                ...$width ? ['w' => $width] : [],
                'fit' => 'crop',
            ]);
        } catch (FileNotFoundException) {
            abort(404);
        }
    }
}