<?php

namespace RalphJSmit\Laravel\Glide;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\ComponentAttributeBag;
use Intervention\Image\ImageManager;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use RuntimeException;

class GlideImageGenerator
{
    public function src(string $path, ?int $maxWidth = null, ?string $sizes = null, bool $lazy = true, ?bool $grow = null, ?string $disk = null): ComponentAttributeBag
    {
        $attributes = new ComponentAttributeBag();

        $grow = $grow ?? config('glide.grow');

        $isGlideSupported = $this->isGlideSupported($path);

        $attributes->setAttributes([
            'src' => $this->getSrcAttribute($path, $maxWidth, $disk),
            ...$isGlideSupported ? ['srcset' => $this->getSrcsetAttribute($path, $maxWidth, $disk)] : [],
            ...($isGlideSupported && $sizes !== null) ? ['sizes' => $sizes] : [],
            ...$lazy ? ['loading' => 'lazy'] : [],
        ]);

        if (! $grow) {
            $attributes = $attributes->style("max-width: {$this->getImageWidth($path, $disk)}px");
        }

        return $attributes;
    }

    protected function getSrcAttribute(string $path, ?int $maxWidth, ?string $disk = null): string
    {
        $resolvedDisk = $disk ?? config('glide.default_source_disk');

        if (! $this->isGlideSupported($path)) {
            return $resolvedDisk
                ? Storage::disk($resolvedDisk)->url($path)
                : asset($path);
        }

        if ($maxWidth === null) {
            return $resolvedDisk
                ? $this->generateUrl($path, [], $disk)
                : asset($path);
        }

        $imageWidth = $this->getImageWidth($path, $disk);

        // For generating the `src` url, we should not use values bigger than the image width, because
        // the browser will load these images at their original size as second request after picking
        // the optimal version. An upsized version should be a convenience thing and not a default.
        return $this->generateUrl($path, [
            'width' => $imageWidth ? min($imageWidth, $maxWidth) : $maxWidth,
        ], $disk);
    }

    protected function getSrcsetAttribute(string $path, ?int $maxWidth, ?string $disk = null): string
    {
        $scale = collect(config('glide.scales'));

        $imageWidth = $this->getImageWidth($path, $disk);

        $upscaleEnabled = config('glide.upscale_enabled');

        $scale = $scale
            ->when($maxWidth)->reject(fn (int $width) => $width > $maxWidth)
            ->when(
                $upscaleEnabled,
                // We will up-scale an image up to 2x it's original size. Above that it has no use anymore.
                fn (Collection $scale) => $scale->when($imageWidth)->reject(fn (int $width) => $width > ($imageWidth * 2)),
                // When upscaling is disabled, we should not generate images larger than the original image.
                fn (Collection $scale) => $scale->when($imageWidth)->reject(fn (int $width) => $width > $imageWidth),
            );

        // Push a final version with exactly the correct max-width if the difference with the last item
        // in the scale is bigger than 50px. Otherwise, the additional provided type is not so useful.
        if ($maxWidth && ($maxWidth - $scale->last()) > 50) {
            // When upscaling is disabled, never push a width larger than the original image.
            $pushWidth = (! $upscaleEnabled && $imageWidth) ? min($maxWidth, $imageWidth) : $maxWidth;

            if (($pushWidth - $scale->last()) > 50) {
                $scale->push($pushWidth);
            }
        }

        // We will push the exact original image width onto the scale so the image is never served
        // smaller than its source. We only do this when the difference with the last item is bigger
        // than 50px. Otherwise, the additional provided source is not so useful.
        if ($imageWidth && ($imageWidth - $scale->last()) > 50) {
            $scale->push($imageWidth);
        }

        return $scale
            ->mapWithKeys(function (int $width) use ($path, $disk, $imageWidth): array {
                // URL-encode each path segment so special characters (spaces, #, ?, &, etc.)
                // in file names work with Glide. We encode per-segment to preserve the slashes.
                $path = implode('/', array_map('rawurlencode', explode('/', $path)));

                if ($imageWidth && $width === $imageWidth) {
                    return [$width => asset($path)];
                }

                return [$width => $this->generateUrl($path, ['width' => $width], $disk)];
            })
            ->map(fn (string $src, int $width) => "{$src} {$width}w")
            ->implode(', ');
    }

    protected function getImageWidth(string $path, ?string $disk = null): ?int
    {
        $cacheKey = "glide::image-generator.image-width.{$disk}.{$path}";

        return Cache::rememberForever($cacheKey, function () use ($path, $disk) {
            return rescue(fn () => $this->getImageManager()->read(
                $this->getSourceFilesystem($disk)->read($path)
            )->width());
        });
    }

    protected function generateUrl(string $path, array $parameters, ?string $disk = null): string
    {
        if ($disk && ! config('glide.route.signed')) {
            throw new RuntimeException('Disk parameter requires signed URLs to be enabled in glide config.');
        }

        $params = ['source' => $path, ...$parameters];

        if ($disk) {
            $params['disk'] = $disk;
        }

        return config('glide.route.signed')
            ? URL::signedRoute('glide.generate', $params)
            : route('glide.generate', $params);
    }

    protected function isGlideSupported(string $path): bool
    {
        return ! Str::endsWith($path, ['.svg']);
    }

    protected function getImageManager(): ImageManager
    {
        if (extension_loaded('gd')) {
            return ImageManager::gd();
        }

        if (extension_loaded('imagick')) {
            return ImageManager::imagick();
        }

        throw new RuntimeException('No supported image driver (GD or Imagick) is installed.');
    }

    public function getSourceFilesystem(?string $disk = null): FilesystemOperator
    {
        $disk ??= config('glide.default_source_disk');

        if (! $disk) {
            return new Filesystem(
                new LocalFilesystemAdapter(public_path())
            );
        }

        return Storage::disk($disk)->getDriver();
    }

    public function getCachePath(): string
    {
        return storage_path('framework/cache/glide');
    }
}
