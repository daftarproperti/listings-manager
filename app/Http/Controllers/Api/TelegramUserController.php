<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Assert;
use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramUserProfileRequest;
use App\Http\Services\GoogleStorageService;
use App\Models\Resources\TelegramUserProfileResource;
use App\Models\TelegramUser;
use App\Models\TelegramUserProfile;
use Illuminate\Http\Resources\Json\JsonResource;

class TelegramUserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tele-app/users/profile",
     *     tags={"Telegram Users"},
     *     summary="Get profile",
     *     description="Returns user profile",
     *     operationId="profile",
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/TelegramUserProfile"
     *         ),
     *     ),
     * )
     */

    public function profile(): JsonResource
    {
        $currentUser = app(TelegramUser::class);
        return new TelegramUserProfileResource($currentUser);
    }

    /**
     * @OA\Post(
     *     path="/api/tele-app/users/profile",
     *     tags={"Telegram Users"},
     *     summary="Update profile",
     *     operationId="updateProfile",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(type="object", ref="#/components/schemas/TelegramUserProfileRequest")
     *          ),
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *              ref="#/components/schemas/TelegramUserProfile"
     *         ),
     *     )
     * )
     */
    public function updateProfile(TelegramUserProfileRequest $request): JsonResource
    {
        $currentUser = app(TelegramUser::class);

        $validatedRequest = $request->validated();

        $pictureUrl = null;
        if (isset($validatedRequest['picture'])) {
            $pictureRequest = $validatedRequest['picture'];

            if (is_object($pictureRequest) && is_a($pictureRequest, \Illuminate\Http\UploadedFile::class)) {
                $googleStorageService = app()->make(GoogleStorageService::class);

                $fileName = sprintf('%s.%s', md5($pictureRequest->getClientOriginalName()) , $pictureRequest->getClientOriginalExtension());
                $fileId = time();
                $googleStorageService->uploadFile(
                    Assert::string(file_get_contents($pictureRequest->getRealPath())),
                    sprintf('%s_%s', $fileId, $fileName)
                );

                $pictureUrl = route('telegram-photo', [$fileId, $fileName]);
            }
        }

        $currentProfile = $currentUser->profile ? (object) $currentUser->profile : null;

        $profile = new TelegramUserProfile();

        $profile->name = Assert::string($validatedRequest['name']);
        $profile->city = isset($validatedRequest['city']) ? Assert::string($validatedRequest['city']) : $currentProfile?->city;
        $profile->description = isset($validatedRequest['description']) ? Assert::string($validatedRequest['description']) : $currentProfile?->description;
        $profile->company = isset($validatedRequest['company']) ? Assert::string($validatedRequest['company']) : $currentProfile?->company;
        $profile->picture = $pictureUrl ?? $currentProfile?->picture ?? null;

        $currentUser->profile = $profile;
        $currentUser->save();

        return new TelegramUserProfileResource($currentUser);
    }
}
