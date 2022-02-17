<?php

namespace MaximilianRadons\LaravelStrapi;



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
