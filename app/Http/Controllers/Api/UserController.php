<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resources\UserResource;
use App\Http\Requests\TelegramUserProfileRequest;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: "/api/app/users/profile",
        tags: ["Telegram Users"],
        summary: "Get profile",
        description: "Returns user profile",
        operationId: "profile",
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(
                    ref: "#/components/schemas/User"
                )
            )
        ]
    )]
    public function profile(): JsonResource
    {
        $currentUser = Auth::user();
        return new UserResource($currentUser);
    }

    #[OA\Post(
        path: "/api/app/users/profile",
        tags: ["Telegram Users"],
        summary: "Update profile",
        operationId: "updateProfile",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(ref: "#/components/schemas/TelegramUserProfileRequest")
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(ref: "#/components/schemas/User")
            )
        ]
    )]
    public function updateProfile(TelegramUserProfileRequest $request): JsonResource
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

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

    #[OA\Post(
        path: "/api/app/users/secret-key",
        tags: ["Telegram Users"],
        summary: "Generate Secret Key for TOTP",
        operationId: "generateSecretKey",
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(ref: "#/components/schemas/User")
            )
        ]
    )]
    public function generateSecretKey(): JsonResource
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        if ($currentUser->secretKey) {
            return new UserResource($currentUser);
        }

        $secretKey = User::generateSecretKey();
        $currentUser->secretKey = $secretKey;
        $currentUser->save();
        return new UserResource($currentUser);
    }

    #[OA\Delete(
        path: "/api/app/users/secret-key",
        tags: ["Telegram Users"],
        summary: "Delete Secret Key for TOTP",
        operationId: "deleteSecretKey",
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(ref: "#/components/schemas/User")
            )
        ]
    )]
    public function deleteSecretKey(): JsonResource
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();
        $currentUser->secretKey = null;
        $currentUser->save();
        return new UserResource($currentUser);
    }
}
