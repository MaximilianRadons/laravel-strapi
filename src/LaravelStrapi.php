<?php

namespace KamilMalinski\LaravelStrapi;


class LaravelStrapi extends LaravelStrapiRequest
{
    public function __construct()
    {
        parent::__construct();
    }

    public function collection(string $type, $sortKey = 'id', $sortOrder = 'DESC', $limit = 20, $start = 0, $fullUrls = true): array
    {
        $url = $type . '?_sort=' . $sortKey . ':' . $sortOrder . '&_limit=' . $limit . '&_start=' . $start;
        $cacheKey = 'collection.' . $type . '.' . $sortKey . '.' . $sortOrder . '.' . $limit . '.' . $start;

        $collection = $this->request('get', $url, $cacheKey, $fullUrls);

        return $collection;
    }

    public function collectionCount(string $type): int
    {
        $url = $type . '/count';
        $cacheKey = 'collectionCount.' . $type;

        $collectionCount = (int) $this->request('get', $url, $cacheKey, false);

        return $collectionCount;
    }

    public function entry(string $type, int $id, $fullUrls = true): array
    {
        $url = $type . '/' . $id;
        $cacheKey = 'entry.' . $type . '.' . $id;

        $entry = $this->request('get', $url, $cacheKey, $fullUrls);

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
        $url = $type . '?filters[' . $fieldName . ']['.$filterOperator.']=' . $fieldValue;
        $cacheKey = 'entryByField.' . $type . '.' . $fieldName . '.' . $fieldValue;

        $entries = $this->request('get', $url, $cacheKey, $fullUrls);

        return $entries;
    }

    public function single(string $type, string $pluck = null, $fullUrls = true)
    {
        $url = $type;
        $cacheKey = 'single.' . $type;

        $single = $this->request('get', $url, $cacheKey, $fullUrls);

        if ($pluck !== null && isset($single[$pluck])) {
            return $single[$pluck];
        }

        return $single;
    }



}
