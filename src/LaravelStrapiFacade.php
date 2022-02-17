<?php

namespace MaximilianRadons\LaravelStrapi;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bbwmc\LaravelStrapi\LaravelStrapi
 */
class LaravelStrapiFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-strapi';
    }
}
