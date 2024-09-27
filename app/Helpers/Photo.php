<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class Photo
{
    public static function getPhotoUrl(string $fileId, string $fileUniqueId): ?string
    {
        $fileName = sprintf('%s_%s', $fileId, $fileUniqueId);

        // Check if file exists in Google Cloud Storage (GCS)
        $gcsPublicUrl = sprintf(
            'https://storage.googleapis.com/%s/%s',
            type(config('services.google.bucket_name'))->asString(),
            $fileName
        );

        $getFileFromGcs = Http::get($gcsPublicUrl);

        if (!$getFileFromGcs->successful()) {
            logger()->warning('File does not exist: ' . $fileName);
        }

        return $fileName;
    }

    /**
     * @param array<string> $pictureUrls
     * @return array<string>
     */
    public static function reformatPictureUrlsIntoGcsUrls(array $pictureUrls): array
    {
        /** @var array<string> $gcsUrls */
        $gcsUrls = [];
        foreach ($pictureUrls as $url) {
            if (str_starts_with($url, 'https://strapi')) {
                // Imported from strapi, just use the URL as is.
                $gcsUrls[] = $url;
                continue;
            }
            $fileName = self::getFileNameFromUrl($url); // need this to handle old data
            $gcsUrls[] = self::getGcsUrlFromFileName($fileName);
        }
        return $gcsUrls;
    }

    /**
     * Get the photo file name from the provided URL.
     *
     * @param string $url The URL from which to extract the photo file name.
     * @return string The extracted photo file name.
     */
    public static function getFileNameFromUrl(string $url): string
    {
        if (str_starts_with($url, 'https://strapi')) {
            // Imported from strapi, save as is.
            return $url;
        }

        $path = explode('/', $url);

        if (strpos($url, 'photo/') !== false) {
            $fileUniqueId = $path[count($path) - 2];
            $fileNameInPath = end($path);
            return sprintf('%s_%s', $fileUniqueId, $fileNameInPath);
        }

        return end($path);
    }

    /**
     * Get the GCS URL from the provided file name.
     * @param string $fileName
     * @return string
     */
    public static function getGcsUrlFromFileName(string $fileName): string
    {
        return sprintf(
            'https://storage.googleapis.com/%s/%s',
            type(config('services.google.bucket_name') ?? '')->asString(),
            $fileName,
        );
    }
}
