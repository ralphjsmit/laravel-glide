<?php

namespace RalphJSmit\Laravel\Glide;

use RalphJSmit\Laravel\Glide\Commands\ClearCacheCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class GlideServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-glide')
            ->hasRoute('web')
            ->hasCommand(ClearCacheCommand::class)
            ->hasConfigFile();
    }
}
