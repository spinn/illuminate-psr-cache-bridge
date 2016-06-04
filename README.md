# PSR-6 cache implementation that connects to Laravel's cache Repository

[![Latest Version on Packagist](https://img.shields.io/packagist/v/madewithlove/illuminate-psr-cache-bridge.svg?style=flat-square)](https://packagist.org/packages/madewithlove/illuminate-psr-cache-bridge)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/madewithlove/illuminate-psr-cache-bridge/master.svg?style=flat-square)](https://travis-ci.org/madewithlove/illuminate-psr-cache-bridge)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/madewithlove/illuminate-psr-cache-bridge.svg?style=flat-square)](https://scrutinizer-ci.com/g/madewithlove/illuminate-psr-cache-bridge)
[![Quality Score](https://img.shields.io/scrutinizer/g/madewithlove/illuminate-psr-cache-bridge.svg?style=flat-square)](https://scrutinizer-ci.com/g/madewithlove/illuminate-psr-cache-bridge)

## Usage

To start using a `Psr\Cache\CacheItemPoolInterface` typed implementation that stores data in Laravel's configured cache, add this to a service provider:

```php
use Illuminate\Contracts\Cache\Repository;
use Madewithlove\IlluminatePsrCacheBridge\Laravel\CacheItemPool;
use Psr\Cache\CacheItemPoolInterface;

$this->app->share(CacheItemPoolInterface::class, function () {
    $repository = $this->app->make(Repository::class);

    return new CacheItemPool($repository);
});
```

Right now you're all set to start injecting `CacheItemPoolInterface`'d everywhere you need it.

## Install

In order to install it via composer you should run this command:

```bash
composer require madewithlove/illuminate-psr-cache-bridge
```

## Testing

``` bash
vendor/bin/phpunit

# or:
vendor/bin/phpunit --testsuite=integration-tests
vendor/bin/phpunit --testsuite=unit-tests
```

## Credits

[All Contributors](https://github.com/madewithlove/illuminate-psr-cache-bridge/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
