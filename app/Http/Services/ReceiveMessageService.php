<?php

namespace App\Http\Services;

use App\Helpers\HousePropertyKeywords;
use App\Models\RawMessage;

class ReceiveMessageService
{
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
}
