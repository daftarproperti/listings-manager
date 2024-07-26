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
                $token = env('IPINFO_TOKEN');
                if (!is_string($token)) return null;

                $locationResponse = $client->request('GET', "https://ipinfo.io/{$ipAddress}/json?token=$token");
                $data = type(json_decode($locationResponse->getBody(), true))->asArray();

                // Extract the latitude and longitude from the 'loc' field
                if (!isset($data['loc'])) return null;
                $location = explode(',', $data['loc']);

                $latLongData = [
                    'latitude' => (float) $location[0],
                    'longitude' => (float) $location[1],
                ];

                return $latLongData;

            } catch (\Throwable $th) {
                logger()->error("Error when getting ip info: " . $th->getMessage());
                return null;
            }
        });

        return $locationData;
    }
}
