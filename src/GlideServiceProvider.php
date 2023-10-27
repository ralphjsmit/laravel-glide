<?php

namespace RalphJSmit\Laravel\Glide;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GlideServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-glide')
            ->hasConfigFile()
            ->hasRoute('web')
            ->hasViews();
    }
}
