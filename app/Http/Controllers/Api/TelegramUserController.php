<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelegramUserProfileRequest;
use App\Models\Resources\TelegramUserProfileResource;
use App\Models\TelegramUser;
use App\Models\TelegramUserProfile;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class TelegramUserController extends Controller
{
    #[OA\Get(
        path: "/api/tele-app/telegram-users/profile",
        tags: ["Telegram Users"],
        summary: "Get profile",
        description: "Returns user profile",
        operationId: "telegramProfile",
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/TelegramUserProfile"
                )
            )
        ]
    )]
    public function profile(): JsonResource
    {
        $currentUser = app(TelegramUser::class);
        return new TelegramUserProfileResource($currentUser);
    }

    #[OA\Post(
        path: "/api/tele-app/telegram-users/profile",
        tags: ["Telegram Users"],
        summary: "Update profile",
        operationId: "updateTelegramProfile",
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                "multipart/form-data" => new OA\MediaType(
                    mediaType: "multipart/form-data",
                    schema: new OA\Schema(ref: "#/components/schemas/TelegramUserProfileRequest")
                )
            ]
        ),
        responses: [
            200 => new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/TelegramUserProfile"
                )
            )
        ]
    )]
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
