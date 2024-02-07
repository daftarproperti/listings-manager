<?php

namespace App\Http\Services;

use App\Helpers\HousePropertyKeywords;
use App\Models\RawMessage;

class ReceiveMessageService
{
    /**
     * @param array<string> $params
     */
    public function saveRawMessage(array $params): ?RawMessage
    {
        if (isset($params['message'])) {
            $message = new RawMessage();
            $message->update_id = $params['update_id'];
            $message->message = $params['message'];
            $message->save();

            return $message;
        }

        return null;
    }

    public function isPropertyInformationMessage(string $message, float $threshold = 25): bool
    {
        $keyWords = HousePropertyKeywords::Keywords();
        $message = strtolower($message);

        $containKeyword = [];
        foreach ($keyWords as $keyWord) {
            if (strpos($message, $keyWord) !== false) {
                $containKeyword[] = $keyWord;
            }
        }

        return (count($containKeyword) / count($keyWords) * 100) >= $threshold;
    }

    /**
     * @param array<array<string>> $photoData
     *
     * @return array<string>
     */
    public function pictureUrls(array $photoData): array
    {
        $pictureUrls = [];

        foreach ($photoData as $photo) {
            $pictureUrls[] = route('telegram-photo', [
                'fileId' => $photo['file_id'],
                'fileUniqueId' => $photo['file_unique_id'],
            ]);
        }

        return $pictureUrls;
    }
}
