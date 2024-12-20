<?php

namespace App\Helpers;

use League\Uri\Http;

class UrlHelper
{
    /**
     * Remove a specific query parameter from an URL.
     *
     * @param string $key The query parameter to remove.
     * @param string $url An url.
     * @return string The updated url without the specified query parameter.
     */
    public static function removeQueryParamFromUrl(string $key, string $url): string
    {
        $uri = Http::new($url);

        // Parse the query string into an array
        parse_str($uri->getQuery(), $queryParameters);

        // Remove the specified query parameter
        unset($queryParameters[$key]);

        // Update the URI with the new query string and return the result as a string
        return $uri->withQuery(http_build_query($queryParameters))->__toString();
    }
}
