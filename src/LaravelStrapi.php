<?php

namespace MaximilianRadons\LaravelStrapi;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use MaximilianRadons\LaravelStrapi\Exceptions\NotFound;
use MaximilianRadons\LaravelStrapi\Exceptions\UnknownError;
use MaximilianRadons\LaravelStrapi\Exceptions\PermissionDenied;

class LaravelStrapi
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

    /**
     * Adds populate relations.
     *
     * @param array $relations
     * @return $this
     */
    public function populate(array $relations): LaravelStrapi
    {
        $this->populate = $relations;

        return $this;
    }

    public function collection(string $type, $sortKey = 'id', $sortOrder = 'DESC', $limit = 20, $start = 0, $fullUrls = true): array
    {
        $url = $this->strapiUrl;
        $cacheKey = self::CACHE_KEY . '.collection.' . $type . '.' . $sortKey . '.' . $sortOrder . '.' . $limit . '.' . $start;

        // Fetch and cache the collection type
        $collection = Cache::remember($cacheKey, $this->cacheTime, function () use ($url, $type, $sortKey, $sortOrder, $limit, $start) {
            $response = Http::get($url . '/api/' . $type . '?_sort=' . $sortKey . ':' . $sortOrder . '&_limit=' . $limit . '&_start=' . $start);

            return $response->json();
        });

        if (isset($collection['statusCode']) && $collection['statusCode'] === 403) {
            Cache::forget($cacheKey);

            throw new PermissionDenied('Strapi returned a 403 Forbidden');
        }

        if (!is_array($collection)) {
            Cache::forget($cacheKey);

            if ($collection === null) {
                throw new NotFound('The requested single entry (' . $type . ') was null');
            }

            throw new UnknownError('An unknown Strapi error was returned');
        }

        // Replace any relative URLs with the full path
        if ($fullUrls) {
            $collection = $this->convertToFullUrls($collection);
        }

        return $collection;
    }

    public function collectionCount(string $type): int
    {
        $url = $this->strapiUrl;

        return Cache::remember(self::CACHE_KEY . '.collectionCount.' . $type, $this->cacheTime, function () use ($url, $type) {
            $response = Http::get($url . '/api/' . $type . '/count');

            return $response->json();
        });
    }

    public function entry(string $type, int $id, $fullUrls = true): array
    {
        $url = $this->strapiUrl;
        $cacheKey = self::CACHE_KEY . '.entry.' . $type . '.' . $id;

        $entry = Cache::remember($cacheKey, $this->cacheTime, function () use ($url, $type, $id) {
            $response = Http::get($url . '/api/' . $type . '/' . $id);

            return $response->json();
        });

        if (isset($entry['statusCode']) && $entry['statusCode'] === 403) {
            Cache::forget($cacheKey);

            throw new PermissionDenied('Strapi returned a 403 Forbidden');
        }

        if (!isset($entry['data'])) {
            Cache::forget($cacheKey);

            if ($entry === null) {
                throw new NotFound('The requested single entry (' . $type . ') was null');
            }

            throw new UnknownError('An unknown Strapi error was returned');
        }

        if ($fullUrls) {
            $entry = $this->convertToFullUrls($entry);
        }

        return $entry;
    }

    /**
     * Filtering
     * Queries can accept a filters parameter with the following syntax:
     *
     * GET /api/:pluralApiId?filters[field][operator]=value
     *
     * The following operators are available:
     *
     * Operator	Description
     * $eq	Equal
     * $ne	Not equal
     * $lt	Less than
     * $lte	Less than or equal to
     * $gt	Greater than
     * $gte	Greater than or equal to
     * $in	Included in an array
     * $notIn	Not included in an array
     * $contains	Contains (case-sensitive)
     * $notContains	Does not contain (case-sensitive)
     * $containsi	Contains
     * $notContainsi	Does not contain
     * $null	Is null
     * $notNull	Is not null
     * $between	Is between
     * $startsWith	Starts with
     * $endsWith	Ends with
     * $or	Joins the filters in an "or" expression
     * $and	Joins the filters in an "and" expression
     *
     * @param string $type
     * @param string $fieldName
     * @param $fieldValue
     * @param string $filterOperator
     * @param bool $fullUrls
     * @return array
     */
    public function entriesByField(string $type, string $fieldName, $fieldValue, string $filterOperator = '$eq', bool $fullUrls = true): array
    {
        $url = $this->strapiUrl;
        $cacheKey = self::CACHE_KEY . '.entryByField.' . $type . '.' . $fieldName . '.' . $fieldValue;

        $entries = Cache::remember($cacheKey, $this->cacheTime, function () use ($url, $type, $fieldName, $fieldValue, $filterOperator) {
            $requestUrl = $url . '/api/' . $type . '?filters[' . $fieldName . ']['.$filterOperator.']=' . $fieldValue;

            if($this->hasPopulates()) {
                $requestUrl .= '&populate=' . $this->preparePopulates();
            }

            $response = Http::get($requestUrl);

            $this->clearPopulates();

            return $response->json();
        });

        if (isset($entries['statusCode']) && $entries['statusCode'] === 403) {
            Cache::forget($cacheKey);

            throw new PermissionDenied('Strapi returned a 403 Forbidden');
        }

        if (!is_array($entries)) {
            Cache::forget($cacheKey);

            if ($entries === null) {
                throw new NotFound('The requested entries by field (' . $type . ') were not found');
            }

            throw new UnknownError('An unknown Strapi error was returned');
        }

        if ($fullUrls) {
            $entries = $this->convertToFullUrls($entries);
        }

        return $entries;
    }

    public function single(string $type, string $pluck = null, $fullUrls = true)
    {
        $url = $this->strapiUrl;
        $cacheKey = self::CACHE_KEY . '.single.' . $type;

        // Fetch and cache the collection type
        $single = Cache::remember($cacheKey, $this->cacheTime, function () use ($url, $type) {

            $requestUrl = $url . '/api/' . $type;

            if($this->hasPopulates()) {
                $requestUrl .= '?populate=' . $this->preparePopulates();
            }

            $response = Http::get($requestUrl);

            $this->clearPopulates();

            return $response->json();
        });

        if (isset($single['statusCode']) && $single['statusCode'] === 403) {
            Cache::forget($cacheKey);

            throw new PermissionDenied('Strapi returned a 403 Forbidden');
        }

        if (! isset($single['data'])) {
            Cache::forget($cacheKey);

            if ($single === null) {
                throw new NotFound('The requested single entry (' . $type . ') was null');
            }

            throw new UnknownError('An unknown Strapi error was returned');
        }

        // Replace any relative URLs with the full path
        if ($fullUrls) {
            $single = $this->convertToFullUrls($single);
        }

        if ($pluck !== null && isset($single[$pluck])) {
            return $single[$pluck];
        }

        return $single;
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
     * Prepares the populates.
     *
     * @return string
     */
    private function preparePopulates(): string
    {
        return implode(',', $this->populate);
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
