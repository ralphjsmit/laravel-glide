<?php

namespace RalphJSmit\Laravel\Glide;

use Illuminate\Support\Facades\Cache;
use Illuminate\View\ComponentAttributeBag;
use Intervention\Image\Facades\Image;

class GlideImageGenerator
{
    public function src(string $path, int $maxWidth = null, string $sizes = null): ComponentAttributeBag
    {
        $attributes = new ComponentAttributeBag();

        $attributes->setAttributes([
            'src' => $this->getSrcAttribute($path, $maxWidth),
            'srcset' => $this->getSrcsetAttribute($path, $maxWidth),
            ...$sizes !== null ? ['sizes' => $sizes] : [],
        ]);

        return $attributes;
    }

    protected function getImageWidth(string $path): ?int
    {
        return Cache::rememberForever("glide::image-generator.image-width.{$path}", function () use ($path) {
            return rescue(fn () => Image::make(public_path($path))->width());
        });
    }

    protected function getSrcAttribute(string $path, int | null $maxWidth): string
    {
        if ( $maxWidth === null ) {
            return asset($path);
        }

        $imageWidth = $this->getImageWidth($path);

        // For generating the `src` url, we should not use values bigger than the image width, because
        // the browser will load these images at their original size as second request after picking
        // the optimal version. An upsized version should be a convenience thing and not a default.
        return $this->generateUrl($path, [
            'width' => $imageWidth ? min($imageWidth, $maxWidth) : $maxWidth,
        ]);
    }

    protected function getSrcsetAttribute(string $path, int | null $maxWidth): string
    {
        $scale = collect([
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
        ]);

        $imageWidth = $this->getImageWidth($path);

        $scale = $scale
            ->when($maxWidth)->reject(fn (int $width) => $width > $maxWidth)
            // We will up-scale an image up to 2x it's original size. Above that it has no use anymore.
            ->when($imageWidth)->reject(fn (int $width) => $width > ($imageWidth * 2));

        return $scale
            ->mapWithKeys(function (int $width) use ($path): array {
                return [$width => $this->generateUrl($path, ['width' => $width])];
            })
            ->map(fn (string $src, int $width) => "{$src} {$width}w")
            ->implode(', ');
    }

    protected function generateUrl(string $path, array $parameters): string
    {
        return route('glide.generate', ['source' => $path, ...$parameters]);
    }
}