# Laravel wrapper for using the Strapi V4 headless CMS

This repository is fork of [MaximilianRadons/laravel-strapi](https://github.com/MaximilianRadons/laravel-strapi), credits goes to [Dave Blakey](https://github.com/dbfx), [BBWMC](https://github.com/bbwmc) and [MaximilianRadons](https://github.com/MaximilianRadons). 

---

Laravel-Strapi is a Laravel helper for using the Strapi V4 headless CMS. 

## Installation

You can install the package via composer:

```bash
composer require kamil-malinski/laravel-strapi
```

You can publish and run the migrations with:

You can publish the config file with:
```bash
php artisan vendor:publish --provider="KamilMalinski\LaravelStrapi\LaravelStrapiServiceProvider" --tag="strapi-config"
```

You need to define your STRAPI_URL (without /api path) and STRAPI_CACHE_TIME in .env: 

```
STRAPI_URL=https://strapi.test.com
STRAPI_CACHE_TIME=3600
```

### Athorization (optional)
Create a bearer token in your admin panel and add it to your .env:
```
STRAPI_API_TOKEN=your_api_token
```

### Cache Flushing (optional)
To flush the strapi cache on content update, you need to create a Webhook in the Strapi admin panel.
Setup a url and a header with Key "Athorization" and a random string as Value and add them to your .env:
```
STRAPI_WEBHOOK_URL=http://localhost:8000/strapi-cache-webhook
STRAPI_WEBHOOK_SIGNATURE=your_random_string
STRAPI_CLEAR_CACHE_ON_WEBHOOK=true //default true
```
In your App you can use the StrapiWebhook Event to implement your own Logic to flush the cash if needed.
Add an Event Listener to your App, you also need to register the Listener in your EventServiceProvider.

## Usage

laravel-strapi provides the collection() and entry() calls to return a full collection, or a specific entry from a collection. In the 
example below we are querying the strapi collection 'blogs' and then getting the entry with id 1 from that collection.
```php
use KamilMalinski\LaravelStrapi\LaravelStrapi;

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
use KamilMalinski\LaravelStrapi\LaravelStrapi;

$strapi = new LaravelStrapi();
$blogs = $strapi->collection('blogs', $sortKey = 'id', $sortOrder = 'DESC', $limit = 20, $start = 0, $fullUrls = true);

$entry = $strapi->entry('blogs', 1, $fullUrls = true);
```

You may also access Single Type items as follows: 

```php
use KamilMalinski\LaravelStrapi\LaravelStrapi;

$strapi = new LaravelStrapi();

// Fetch the full homepage array
$homepageArray = $strapi->single('homepage');

// Return just the ['content'] field from the homepage array
$homepageItem = $strapi->single('homepage', 'content');
```

And you may select entries by searching for a custom field (e.g. slug): 

```php
use KamilMalinski\LaravelStrapi\LaravelStrapi;

$strapi = new LaravelStrapi();

$entries = $strapi->entriesByField('blogs', 'slug', 'test-blog-post');
```

Populate with deep relations:

```php
use KamilMalinski\LaravelStrapi\LaravelStrapi;

$strapi = new LaravelStrapi();

// load all relations one Level deep 
$homepageArray = $strapi->populate(['*'])->single('homepage');

// 2 Level or deeper example
$homepageArray = $strapi->populate(['homepage.navbar.links', 'homepage.metadata'])->single('homepage');
```

Deeply populate a dynamic zone with 2 components, examples from [Strapi API Docs](https://docs.strapi.io/developer-docs/latest/developer-resources/database-apis-reference/rest/populating-fields.html#component-dynamic-zones):

```php
use KamilMalinski\LaravelStrapi\LaravelStrapi;

$strapi = new LaravelStrapi();

$query = [
    'populate' => [
        'testDZ' => [
            'populate' => '*'
        ],
    ]
];

$homepageArray = $strapi->query($query)->single('articels');

$query = [
    'fields' => ['title', 'slug'],
    'populate' => [
        'headerImage' => [
            'fields' => ['name', 'url']
        ],
    ]
];

$homepageArray = $strapi->query($query)->single('articels');

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Credits

- [Dave Blakey](https://github.com/dbfx)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
