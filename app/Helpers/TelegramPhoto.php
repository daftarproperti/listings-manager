<?php

namespace App\Helpers;

use App\Http\Services\GoogleStorageService;
use Illuminate\Support\Facades\Http;

class TelegramPhoto
{
    public static function getPhotoUrl(string $fileId, string $fileUniqueId): ?string
    {
        $fileName = sprintf('%s_%s', $fileId, $fileUniqueId);

        // Check if file exists in Google Cloud Storage (GCS)
        $gcsPublicUrl = sprintf(
            'https://storage.googleapis.com/%s/%s',
            Assert::string(config('services.google.bucket_name')),
            $fileName
        );

        $getFileFromGcs = Http::get($gcsPublicUrl);

        if (!$getFileFromGcs->successful()) {
            // If not found in GCS, retrieve file info from Telegram
            $fileInfoEndpoint = sprintf(
                'https://api.telegram.org/bot%s/getFile',
                Assert::string(config('services.telegram.bot_token'))
            );

            $fileInfoRequest = Http::get($fileInfoEndpoint, [
                'file_id' => $fileId,
                'file_unique_id' => $fileUniqueId
            ]);

            if ($fileInfoRequest->successful()) {
                $fileInfo = (array) $fileInfoRequest->json('result');

                // If file info obtained, upload file to GCS
                if (isset($fileInfo['file_path'])) {
                    $telegramFileUrl = sprintf(
                        'https://api.telegram.org/file/bot%s/%s',
                        Assert::string(config('services.telegram.bot_token')),
                        Assert::string($fileInfo['file_path'])
                    );
                    $googleStorageService = new GoogleStorageService();
                    $googleStorageService->uploadFile(Assert::string(file_get_contents($telegramFileUrl)), $fileName);
                }
            }
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
                $fileName = TelegramPhoto::getFileNameFromUrl($url); // need this to handle old data
                $gcsUrls[] = TelegramPhoto::getGcsUrlFromFileName($fileName);
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
        $path = explode('/', $url);

        if (strpos($url, 'photo/') !== false) {
            $fileUniqueId = Assert::string($path[count($path) - 2]);
            $fileNameInPath = Assert::string(end($path));
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
            Assert::string(config('services.google.bucket_name') ?? ''),
            Assert::string($fileName)
        );
    }
}
