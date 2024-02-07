<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TelegramPhoto;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoController extends Controller
{
    public function telegramPhoto(string $fileId, string $fileUniqueId): \Illuminate\Http\Response|StreamedResponse
    {
        $photoUrl = TelegramPhoto::getPhotoUrl($fileId, $fileUniqueId);
        $fileContent = $photoUrl ? file_get_contents($photoUrl) : null;

        return $fileContent ? Response::stream(function() use($fileContent) {
            print $fileContent;
        }, 200, ['Content-type' => 'image/jpeg']) : Response::make(null, 404);
    }
}
