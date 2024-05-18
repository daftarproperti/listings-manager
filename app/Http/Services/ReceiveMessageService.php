<?php

namespace App\Http\Services;

use App\DTO\Telegram\PhotoSize;
use App\DTO\Telegram\Update;
use App\Helpers\ClassificationKeyword;
use App\Http\Services\ClassificationService;
use App\Models\Enums\MessageClassification;
use App\Models\RawMessage;
use Illuminate\Support\Facades\Log;

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

    public function classifyMessage(string $message, float $threshold = 25): ?string
    {
        $classificationEnabled = type(config('services.msg_classification.enabled'))->asBool();
        if ($classificationEnabled) {
            return $this->determineFromClassificationAPI($message);
        }

        return $this->determineFromKeywords($message, $threshold);
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

    private function determineFromClassificationAPI(string $message): ?string
    {
        $classificationService = new ClassificationService();

        try {
            $result = $classificationService->classify($message);
            return MessageClassification::from($result)->value;
        } catch (\Exception $e) {
            Log::error("Error caught when trying to classify message:" . $e->getMessage());
        }

        return null;
    }

    private function determineFromKeywords(string $message, float $threshold = 25): ?string
    {
        $propertyKeywords = ClassificationKeyword::PropertyKeywords();
        $isProperty = $this->classifiedByKeywords($message, $propertyKeywords, $threshold);

        if ($isProperty) {
            return MessageClassification::LISTING->value;
        }

        $buyerRequestKeywords = ClassificationKeyword::BuyerRequestKeywords();
        $isBuyerRequest = $this->classifiedByKeywords($message, $buyerRequestKeywords, $threshold);

        if ($isBuyerRequest) {
            return MessageClassification::BUYER_REQUEST->value;
        }

        return null;
    }

    /**
     * @param array<string> $keyWords
     */
    private function classifiedByKeywords(string $message, array $keyWords, float $threshold = 25): bool
    {
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
