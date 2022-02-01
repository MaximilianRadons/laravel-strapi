<?php

namespace Bbwmc\LaravelStrapi;

use Bbwmc\LaravelStrapi\Commands\LaravelStrapiCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelStrapiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-strapi')
            ->hasConfigFile()
            ->hasCommand(LaravelStrapiCommand::class);
    }
}
