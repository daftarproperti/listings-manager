<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramUserProfileRequest;
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
        /** @var TelegramUser $currentUser */
        $currentUser = app(TelegramUser::class);

        $validatedRequest = $request->validated();

        $mergedArray = array_merge($currentUser->profile?->toArray() ?? [], $validatedRequest);
        $currentUser->profile = TelegramUserProfile::from($mergedArray);
        $currentUser->save();

        return new TelegramUserProfileResource($currentUser);
    }
}
