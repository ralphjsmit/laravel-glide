<?php

namespace RalphJSmit\Laravel\Glide;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;
use Intervention\Image\Facades\Image;

class GlideImageGenerator
{
    public function src(string $path, ?int $maxWidth = null, ?string $sizes = null, bool $lazy = true, bool $grow = false): ComponentAttributeBag
    {
        $attributes = new ComponentAttributeBag();

        $isGlideSupported = $this->isGlideSupported($path);

        $attributes->setAttributes([
            'src' => $this->getSrcAttribute($path, $maxWidth),
            ...$isGlideSupported ? ['srcset' => $this->getSrcsetAttribute($path, $maxWidth)] : [],
            ...$grow ? [] : ['style' => "max-width: {$this->getImageWidth($path)}px"],
            ...($isGlideSupported && $sizes !== null) ? ['sizes' => $sizes] : [],
            ...$lazy ? ['loading' => 'lazy'] : [],
        ]);

        return $attributes;
    }

    protected function getSrcAttribute(string $path, ?int $maxWidth): string
    {
        if (! $this->isGlideSupported($path)) {
            return asset($path);
        }

        if ($maxWidth === null) {
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

    protected function getSrcsetAttribute(string $path, ?int $maxWidth): string
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
            7000,
            8000,
            9000,
            10000,
        ]);

        $imageWidth = $this->getImageWidth($path);

        $scale = $scale
            ->when($maxWidth)->reject(fn (int $width) => $width > $maxWidth)
            // We will up-scale an image up to 2x it's original size. Above that it has no use anymore.
            ->when($imageWidth)->reject(fn (int $width) => $width > ($imageWidth * 2));

        // Push a final version with exactly the correct max-width if the difference with the last item
        // in the scale is bigger than 50px. Otherwise, the additional provided type is not so useful.
        if ($maxWidth && ($maxWidth - $scale->last()) > 50) {
            $scale->push($maxWidth);
        }

        return $scale
            ->mapWithKeys(function (int $width) use ($path): array {
                return [$width => $this->generateUrl($path, ['width' => $width])];
            })
            ->map(fn (string $src, int $width) => "{$src} {$width}w")
            ->implode(', ');
    }

    protected function getImageWidth(string $path): ?int
    {
        return Cache::rememberForever("glide::image-generator.image-width.{$path}", function () use ($path) {
            return rescue(fn () => Image::make(public_path($path))->width());
        });
    }

    protected function generateUrl(string $path, array $parameters): string
    {
        return route('glide.generate', ['source' => $path, ...$parameters]);
    }

    protected function isGlideSupported(string $path): bool
    {
        return ! Str::endsWith($path, ['.svg']);
    }

    public function getSourcePath(): string
    {
        return public_path();
    }

    public function getCachePath(): string
    {
        return storage_path('framework/cache/glide');
    }
}
