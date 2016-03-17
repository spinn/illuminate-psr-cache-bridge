<?php
namespace Madewithlove\LaravelPsrCacheBridge\Exceptions;

use Exception;
use Psr\Cache\InvalidArgumentException as InvalidArgumentExceptionContract;

class InvalidArgumentException extends Exception implements InvalidArgumentExceptionContract
{
}
