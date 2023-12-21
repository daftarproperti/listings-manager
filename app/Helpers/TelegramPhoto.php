<?php
namespace App\Helpers;

use App\Http\Services\GoogleStorageService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramPhoto
{
    public static function getPhotoUrl($fileId, $fileUniqueId): ?string
    {
        $telegramFileUrl = null;

        // check file in bucket
        $googleStorageService = app()->make(GoogleStorageService::class);
        $fileName = sprintf('%s_%s', $fileId, $fileUniqueId);
        $fileItem = $googleStorageService->getFile($fileName);

        if ($fileItem->exists()) {
            return $fileItem->signedUrl(time() + 3600);
        }

        try {
            $fileInfoEndpoint = sprintf('https://api.telegram.org/bot%s/getFile', config('services.telegram.bot_token'));
            $fileInfoRequest = Http::get($fileInfoEndpoint, [
                'file_id' => $fileId,
                'file_unique_id' => $fileUniqueId
            ]);

            if ($fileInfoRequest->successful()) {
                $fileInfo = $fileInfoRequest->json('result');
                if (isset($fileInfo['file_path'])) {
                    $telegramFileUrl = sprintf('https://api.telegram.org/file/bot%s/%s', config('services.telegram.bot_token'), $fileInfo['file_path']);
                    //put file in bucket
                    $googleStorageService->uploadFile(file_get_contents($telegramFileUrl), $fileName);
                }
            }

        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }

        return $telegramFileUrl;
    }

}
