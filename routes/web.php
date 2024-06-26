<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use MaximilianRadons\LaravelStrapi\Events\StrapiWebhook;

Route::post(config('strapi.webhook_url'), function (Request $request) {

    if(request()->header(config('strapi.webhook_signature_header')) != config('strapi.webhook_signature')){
        abort(401);
    }

    if(config('strapi.clear_cache_on_webhook') && Cache::has(config('strapi.cache_prefix').'.keys')){
        $cache_keys = Cache::get(config('strapi.cache_prefix').'.keys');

        foreach($cache_keys as $key){
            Cache::forget($key);
        }

        Cache::forget(config('strapi.cache_prefix').'.keys');
    }

    StrapiWebhook::dispatch($request);

    return response('ok', 200);
});