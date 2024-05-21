<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resources\UserResource;
use App\Http\Requests\TelegramUserProfileRequest;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
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
        $currentUser = app(User::class);
        return new UserResource($currentUser);
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
        /** @var User $currentUser */
        $currentUser = app(User::class);

        $validatedRequest = $request->validated();
        foreach ($validatedRequest as $key => $value) {
            if ($key === 'phoneNumber') {
                // Since phone number is used as identifier, it will not be updated
                continue;
            }
            $currentUser->$key = $value;
        }

        $currentUser->save();

        return new UserResource($currentUser);
    }
}
