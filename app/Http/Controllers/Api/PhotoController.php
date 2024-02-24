<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Assert;
use App\Helpers\TelegramPhoto;
use App\Http\Controllers\Controller;
use App\Http\Services\GoogleStorageService;
use Illuminate\Http\Request;
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

    public function uploadImage(Request $request): \Illuminate\Http\Response|StreamedResponse
    {
        // Validate the incoming request data
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust maximum file size as needed
        ]);

        // Retrieve the uploaded image
        $image = $request->file('image');

        $googleStorageService = app()->make(GoogleStorageService::class);
        if (is_object($image) && is_a($image, \Illuminate\Http\UploadedFile::class)) {
            $fileName = sprintf('%s.%s', md5($image->getClientOriginalName()) , $image->getClientOriginalExtension());
            $fileId = time();
            $googleStorageService->uploadFile(
                Assert::string(file_get_contents($image->getRealPath())),
                sprintf('%s_%s', $fileId, $fileName)
            );
            $imageUrl = route('telegram-photo', [$fileId, $fileName]);
        } else {
            $imageUrl = Assert::string($image);
        }

        // Return a response indicating success, or do something else with the uploaded images
        return $imageUrl ? Response::stream(function() use($imageUrl) {
            print $imageUrl;
        }, 200, ['image_url' => $imageUrl]) : Response::make(null, 404);
    }
}
