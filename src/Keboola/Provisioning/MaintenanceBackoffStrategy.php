<?php

namespace Keboola\Provisioning;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Exception\HttpException;
use Guzzle\Plugin\Backoff\AbstractErrorCodeBackoffStrategy;

/**
 * Strategy used to retry HTTP requests based on the response code.
 *
 * Retries 500 and 503 error by default.
 */
class MaintenanceBackoffStrategy extends AbstractErrorCodeBackoffStrategy
{
    /** @var array Default HTTP codes errors to retry */
    protected static $defaultErrorCodes = array(503);

    /** @var array Default HTTP codes errors to retry */
    protected static $defaultRetryAfter = 60;

    protected function getDelay($retries, RequestInterface $request, Response $response = null, HttpException $e = null)
    {
        if ($response) {
            //Short circuit the rest of the checks if it was successful
            if ($response->isSuccessful()) {
                return false;
            } else {
                if(isset($this->errorCodes[$response->getStatusCode()])) {
                    if ($response->getHeader("Retry-After")) {
                        return $response->getHeader("Retry-After")->__toString();
                    } else {
                        return self::$defaultRetryAfter;
                    }
                } else {
                    return null;
                }
            }
        }
    }
}
