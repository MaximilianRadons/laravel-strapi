# Laravel wrapper for using the Strapi headless CMS

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bbwmc/laravel-strapi.svg?style=flat-square)](https://packagist.org/packages/bbwmc/laravel-strapi)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/bbwmc/laravel-strapi/run-tests?label=tests)](https://github.com/bbwmc/laravel-strapi/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/bbwmc/laravel-strapi/Check%20&%20fix%20styling?label=code%20style)](https://github.com/bbwmc/laravel-strapi/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/bbwmc/laravel-strapi.svg?style=flat-square)](https://packagist.org/packages/bbwmc/laravel-strapi)

---

Laravel-Strapi is a Laravel helper for using the Strapi headless CMS. 

## Installation

You can install the package via composer:

```bash
composer require bbwmc/laravel-strapi
```

You can publish and run the migrations with:

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Bbwmc\LaravelStrapi\LaravelStrapiServiceProvider" --tag="strapi-config"
```

You need to define your STRAPI_URL and STRAPI_CACHE_TIME in .env: 

```
STRAPI_URL=https://strapi.test.com
STRAPI_CACHE_TIME=3600
```

## Usage

laravel-strapi provides the collection() and entry() calls to return a full collection, or a specific entry from a collection. In the 
example below we are querying the strapi collection 'blogs' and then getting the entry with id 1 from that collection.
```php
use Bbwmc\LaravelStrapi\LaravelStrapi;

$strapi = new LaravelStrapi();
$blogs = $strapi->collection('blogs');
$entry = $strapi->entry('blogs', 1);
```

There are several useful options available as well. 

- ```$sortKey``` and ```$sortOrder``` allow you to specify the key to sort on and the direction
- ```$fullUrls``` will automatically add your STRAPI_URL to the front of any relative URLs (e.g. images, etc).
- ```$limit``` sets how many items you are requesting
- ```$start``` is the offset to be used with limit, useful for pagination

```php
use Bbwmc\LaravelStrapi\LaravelStrapi;

$strapi = new LaravelStrapi();
$blogs = $strapi->collection('blogs', $sortKey = 'id', $sortOrder = 'DESC', $limit = 20, $start = 0, $fullUrls = true);

$entry = $strapi->entry('blogs', 1, $fullUrls = true);
```

You may also access Single Type items as follows: 

```php
use Bbwmc\LaravelStrapi\LaravelStrapi;

$strapi = new LaravelStrapi();

// Fetch the full homepage array
$homepageArray = $strapi->single('homepage');

// Return just the ['content'] field from the homepage array
$homepageItem = $strapi->single('homepage', 'content');
```

And you may select entries by searching for a custom field (e.g. slug): 

```php
use Bbwmc\LaravelStrapi\LaravelStrapi;

$strapi = new LaravelStrapi();

$entries = $strapi->entriesByField('blogs', 'slug', 'test-blog-post');
```

## Limitations

This is primarily built around public content (so far). It doesn't yet support authentication, etc. Please consider contributing!

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Credits

- [Dave Blakey](https://github.com/bbwmc)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
