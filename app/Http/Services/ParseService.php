<?php

namespace App\Http\Services;

use App\DTO\Telegram\Message;
use App\DTO\Telegram\Update;
use App\Helpers\Queue;
use App\Helpers\TelegramInteractionHelper;
use App\Jobs\ParseBuyerRequestJob;
use App\Jobs\ParseListingJob;
use App\Models\ListingUser;
use App\Models\TelegramUser;

class ParseService
{
    public function parseListing(Update $update): void
    {
        $receiveMessageService = new ReceiveMessageService();
        $rawMessage = $receiveMessageService->saveRawMessage($update);

        $pictureUrls = [];
        if (!empty($update->message->photo)) {
            $pictureUrls = $receiveMessageService->pictureUrls($update->message->photo);
        }

        $chatId = isset($update->message->chat) ? $update->message->chat->id : null;
        $listingUser = $this->populateListingUser($update->message);

        $emptyProfile = false;
        $telegramUser = TelegramUser::where('user_id', $listingUser->userId)->first();
        if ($telegramUser) {
            $userProfile = $telegramUser->profile;
            if (!$userProfile || !property_exists($telegramUser, 'profile')) {
                $emptyProfile = true;
            }
        }

        ParseListingJob::dispatch(
            $update->message?->text,
            $pictureUrls,
            $listingUser,
            $chatId,
            $rawMessage
        )->onQueue(Queue::getQueueName('generic'));

        $isPrivateChat = isset($update->message->chat) && $update->message->chat->type == 'private';

        if ($chatId && $isPrivateChat) {
            $message = 'Terima kasih atas Listing yang anda bagikan.' . "\n\n" . 'Informasi sedang kami proses dan masukkan ke database...';
            if ($emptyProfile) {
                $message = $message . "\n\n" . 'Agar listing lebih dapat ditemukan pencari, silahkan lengkapi data diri anda melalui Kelola Listing -> Akun';
            }

            TelegramInteractionHelper::sendMessage(
                $chatId,
                $message
            );
        }
    }

    public function parseBuyerRequest(Update $update): void
    {
        $receiveMessageService = new ReceiveMessageService();
        $receiveMessageService->saveRawMessage($update);

        $listingUser = $this->populateListingUser($update->message);

        ParseBuyerRequestJob::dispatch(
            $update->message?->text,
            $listingUser
        )->onQueue(Queue::getQueueName('generic'));

        return;
    }

    private function populateListingUser(?Message $message): ListingUser
    {
        $listingUser = new ListingUser();
        $listingUser->name = trim(sprintf(
            '%s %s',
            $message?->from->first_name ?? '',
            $message?->from->last_name ?? ''
        ));
        $listingUser->userName = $message?->from->username ?? null;
        $listingUser->userId = $message?->from->id ?? 0;
        $listingUser->source = 'telegram';

        return $listingUser;
    }
}
