<?php
namespace Madewithlove\IlluminatePsrCacheBridge\Exceptions;

use Exception;
use Psr\Cache\InvalidArgumentException as InvalidArgumentExceptionContract;

class InvalidArgumentException extends Exception implements InvalidArgumentExceptionContract
{
}
