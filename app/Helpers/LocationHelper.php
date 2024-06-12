<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class LocationHelper
{
    /**
     * Get latitude and longitude by IP address
     * @param string $ipAddress
     * @return ?array<string, float>
     */
    public static function getLatLongByIpAddress(string $ipAddress = '127.0.0.1'): ?array
    {
        $locationData = Cache::remember('location_' . $ipAddress, now()->addWeek(), function () use ($ipAddress) {
            try {
                $client = new Client();

                // Check if the request comes from localhost
                if ($ipAddress === '127.0.0.1' || $ipAddress === '::1') {
                    // Fetch the actual ISP IP address using ipify.org if the request is from localhost
                    $ipResponse = $client->request('GET', 'https://api64.ipify.org?format=json');
                    $ipData = type(json_decode($ipResponse->getBody(), true))->asArray();
                    $ipAddress = $ipData['ip'];
                }

                // Send a GET request to the ipinfo.io API with the determined IP
                $locationResponse = $client->request('GET', "http://ipinfo.io/{$ipAddress}/json");
                $data = type(json_decode($locationResponse->getBody(), true))->asArray();

                // Extract the latitude and longitude from the 'loc' field
                $location = explode(',', $data['loc']);

                $latLongData = [
                    'latitude' => (float) $location[0],
                    'longitude' => (float) $location[1],
                ];

                return $latLongData;

            } catch (\Throwable $th) {
                return null;
            }
        });

        return $locationData;
    }
}
