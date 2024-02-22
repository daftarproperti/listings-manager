<?php

namespace App\Http\Services;

use App\DTO\Telegram\PhotoSize;
use App\DTO\Telegram\Update;
use App\Helpers\HousePropertyKeywords;
use App\Models\RawMessage;

class ReceiveMessageService
{
    /**
     * @param Update $update
     */
    public function saveRawMessage(Update $update): ?RawMessage
    {
        if (isset($update->message)) {
            $message = new RawMessage();
            $message->update_id = $update->update_id;
            $message->message = $update->message;
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
     * @param array<PhotoSize> $photoData
     *
     * @return array<string>
     */
    public function pictureUrls(array $photoData): array
    {
        $pictureUrls = [];

        foreach ($photoData as $photoSize) {
            $pictureUrls[] = route('telegram-photo', [
                'fileId' => $photoSize->file_id,
                'fileUniqueId' => $photoSize->file_unique_id,
            ]);
        }

        return $pictureUrls;
    }
}
