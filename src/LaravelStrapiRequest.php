<?php
namespace KamilMalinski\LaravelStrapi;

use stdClass;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use KamilMalinski\LaravelStrapi\Exceptions\NotFound;
use KamilMalinski\LaravelStrapi\Exceptions\UnknownError;
use KamilMalinski\LaravelStrapi\Exceptions\PermissionDenied;


class LaravelStrapiRequest
{
    private string $strapiUrl;
    private int $cacheTime;
    private array $populate;
    private $query;

    public function __construct()
    {
        $this->strapiUrl = config('strapi.url');
        $this->cacheTime = config('strapi.cache_time');
        $this->populate = [];
        $this->query = null;
    }  

    protected function request(string $verb, string $url, string $cacheKey, bool $fullUrls)
    {
        $url = $this->strapiUrl . '/api/'. $url;

        if($this->hasPopulates()) {           
            if(str_contains($url,'?')){
                $url .= '&' . $this->preparePopulates();
            }else{
                $url .= '?' . $this->preparePopulates();
            }            
        }

        if(!$this->hasPopulates() && $this->hasQuery()){
            if(str_contains($url,'?')){
                $url .= '&' . $this->prepareQuery();
            }else{
                $url .= '?' . $this->prepareQuery();
            } 
        }

        $cacheKey = config('strapi.cache_prefix') . '.' . $cacheKey . '.' . hash("md5", $url);
        $this->rememberCacheKeys($cacheKey);
        
        $data = Cache::remember($cacheKey, $this->cacheTime, function () use ($verb, $url) {
           
            //echo urldecode( $url )."<br><br>";

            $response = Http::withToken(config('strapi.token'))->$verb($url);

            $this->clearPopulates();
            $this->clearQuery();

            return $response->json();
        });

        if (isset($data['statusCode']) && $data['statusCode'] === 403) {
            Cache::forget($cacheKey);

            throw new PermissionDenied('Strapi returned a 403 Forbidden');
        }

        if (!is_array($data)) {
            Cache::forget($cacheKey);

            if ($data === null) {
                throw new NotFound('The requested entries were not found ' . $url);
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

        $populate = [
            'populate' => json_decode(json_encode($this->populate))
        ];

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

    /**
     * Adds populate query.
     *
     * @param object $query
     * @return $this
     */
    public function query($query): LaravelStrapiRequest
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Prepares the query.
     *
     * @return string
     */
    private function prepareQuery()
    {
        return http_build_query($this->query);
    }

    /**
     * Check if there is a query present.
     *
     * @return bool
     */
    private function hasQuery(): bool
    {
        return !empty($this->query);
    }

    /*
     * Clears the query.
     */
    private function clearQuery(): void
    {
        $this->query = null;
    }

    /*
     * Remember all cache keys to flush strapi cache only
     * 
     */
    public function rememberCacheKeys($cacheKey)
    {
        if(Cache::has(config('strapi.cache_prefix').'.keys')){
            $cached_keys = Cache::get(config('strapi.cache_prefix').'.keys');
            if(!in_array($cacheKey, $cached_keys)){
                $cached_keys[] = $cacheKey;
            }
            Cache::put(config('strapi.cache_prefix').'.keys', $cached_keys);
        }else{
            Cache::put(config('strapi.cache_prefix').'.keys', [$cacheKey]);
        }
    }

}