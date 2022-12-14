<?php

namespace KamilMalinski\LaravelStrapi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use KamilMalinski\LaravelStrapi\Commands\LaravelStrapiCommand;



class LaravelStrapiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-strapi')
            ->hasConfigFile()
            ->hasRoute('web')
            ->hasCommand(LaravelStrapiCommand::class);
    }
}
