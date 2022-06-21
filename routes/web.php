<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::post(config('strapi.webhook_url'), function (Request $request) {

    if(request()->header(config('strapi.webhook_signature_header')) != config('strapi.webhook_signature')){
        abort(401);
    }

    if(!Cache::has(config('strapi.cache_prefix').'.keys')){
        return response('ok', 200);
    }

    $cache_keys = Cache::get(config('strapi.cache_prefix').'.keys');

    foreach($cache_keys as $key){
        Cache::forget($key);
    }

    Cache::forget(config('strapi.cache_prefix').'.keys');

    //Log::info($request->all());

    return response('ok', 200);
});