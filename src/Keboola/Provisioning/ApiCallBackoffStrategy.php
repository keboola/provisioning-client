<?php

namespace Keboola\Provisioning;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Http\Exception\HttpException;
use Guzzle\Plugin\Backoff\AbstractErrorCodeBackoffStrategy;

/**
 * Strategy used to retry HTTP requests based on the response code.
 *
 * Retries 500, 502, 503 and 504 errors by default.
 */
class ApiCallBackoffStrategy extends AbstractErrorCodeBackoffStrategy
{
    /** @var array Default HTTP codes errors to retry */
    protected static $defaultErrorCodes = array(502, 503, 504, 500);

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
