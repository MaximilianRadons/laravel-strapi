<?php
namespace MaximilianRadons\LaravelStrapi;

use stdClass;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use MaximilianRadons\LaravelStrapi\LaravelStrapi;
use MaximilianRadons\LaravelStrapi\Exceptions\NotFound;
use MaximilianRadons\LaravelStrapi\Exceptions\UnknownError;
use MaximilianRadons\LaravelStrapi\Exceptions\PermissionDenied;


class LaravelStrapiRequest
{
    public const CACHE_KEY = 'laravel-strapi-cache';

    private string $strapiUrl;
    private int $cacheTime;
    private array $populate;

    public function __construct()
    {
        $this->strapiUrl = config('strapi.url');
        $this->cacheTime = config('strapi.cacheTime');
        $this->populate = [];
    }  

    protected function request(string $verb, string $url, string $cacheKey, bool $fullUrls)
    {
        $url = $this->strapiUrl . '/api/'. $url;
        
        $data = Cache::remember($cacheKey, $this->cacheTime, function () use ($verb, $url) {
           
            if($this->hasPopulates()) {
                
                if(str_contains($url,'?')){
                    $url .= '&' . $this->preparePopulates();
                }else{
                    $url .= '?' . $this->preparePopulates();
                }
                
            }
          
            $response = Http::$verb($url);

            $this->clearPopulates();

            return $response->json();
        });

        if (isset($data['statusCode']) && $data['statusCode'] === 403) {
            Cache::forget($cacheKey);

            throw new PermissionDenied('Strapi returned a 403 Forbidden');
        }

        if (!is_array($data)) {
            Cache::forget($cacheKey);

            if ($data === null) {
                throw new NotFound('The requested entries were not found');
            }

            throw new UnknownError('An unknown Strapi error was returned');
        }

        if ($fullUrls) {
            $data = $this->convertToFullUrls($data);
        }

        return $data;
    }

    /**
     * This function adds the Strapi URL to the front of content in entries, collections, etc.
     * This is primarily used to change image URLs to actually point to Strapi.
     */
    private function convertToFullUrls($array): array
    {
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $array[$key] = $this->convertToFullUrls($item);
            }

            if (!is_string($item) || empty($item)) {
                continue;
            }

            $array[$key] = preg_replace('/!\[(.*)\]\((.*)\)/', '![$1](' . config('strapi.url') . '$2)', $item);
        }

        return $array;
    }

    /**
     * Adds populate relations.
     *
     * @param array $relations
     * @return $this
     */
    public function populate(array $relations): LaravelStrapiRequest
    {
        $this->populate = $relations;

        return $this;
    }

    /**
     * Prepares the populates.
     *
     * @return string
     */
    private function preparePopulates()
    {
        if($this->populate[0] == '*'){
            return 'populate=*';
        }

        $populate = new stdClass();
        $populate->populate =  json_decode(json_encode($this->populate));

        return http_build_query($populate);
    }

    /**
     * Check if there populates present.
     *
     * @return bool
     */
    private function hasPopulates(): bool
    {
        return !empty($this->populate);
    }

    /*
     * Clears the populates.
     */
    private function clearPopulates(): void
    {
        $this->populate = [];
    }

}