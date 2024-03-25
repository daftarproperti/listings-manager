<?php

namespace App\Helpers;

class Queue
{
    /**
     * Get queue name based on DP_VERSION.
     *
     * If version is set (not 0), we should dispatch job to a specific version queue to ensure compatibility.
     * If version is not set (or 0), dispatch job to 'default' queue (for development compatibility is not important).
     *
     * Prefix is useful to differentiate different queue purposes, otherwise $prefix can be 'generic'.
     */
    public static function getQueueName(string $prefix): ?string
    {
        $version = config('app.version');
        return (is_string($version) && $version) ? "$prefix-$version" : null;
    }
}
