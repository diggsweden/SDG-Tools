<?php

namespace Piwik\Plugins\SDGWebStatistics\services;

use Piwik\Http;

/**
 * Wraps matomos Http
 */
class HttpService 
{
    /**
     * Send http request
     * 
     * @param string $url The request url
     * @param string $httpMethod i.e "GET" or "POST"
     * @param string[] $headers The request headers in the format array("<header-name>": <header-value>, ...)
     * @param string|null  $requestBody The request body
     * 
     * @return array Containing the data, status and headers of the request. If the request was unsuccessful data will be false.
     */
    public function sendHttpRequest(string $url, string $httpMethod, array $headers, $requestBody): array {
        return Http::sendHttpRequestBy(
            "curl",
            $url,
            10,
            null,
            null,
            null,
            0,
            false,
            false,
            false,
            true,
            $httpMethod,
            null,
            null,
            $requestBody,
            $headers);
    }
}