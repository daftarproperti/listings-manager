<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TelegramPhoto;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;

class PhotoController extends Controller
{
    public function telegramPhoto($fileId, $fileUniqueId)
    {
        $photoUrl = TelegramPhoto::getPhotoUrl($fileId, $fileUniqueId);
        $fileContent = $photoUrl ? file_get_contents($photoUrl) : null;

        return $fileContent ? Response::stream(function() use($fileContent) {
            print $fileContent;
        }, 200, ['Content-type' => 'image/jpeg']) : Response::make(null, 404);
    }
}
