<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DPAuth;
use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\Resources\UserResource;
use App\Http\Requests\UserProfileRequest;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(
        path: '/api/app/users/profile',
        tags: ['Users'],
        summary: 'Get profile',
        description: 'Returns user profile',
        operationId: 'profile',
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/User',
                ),
            ),
        ],
    )]
    public function profile(): JsonResource
    {
        $currentUser = DPAuth::appUser();
        return new UserResource($currentUser);
    }

    #[OA\Post(
        path: '/api/app/users/profile',
        tags: ['Users'],
        summary: 'Update profile',
        operationId: 'updateProfile',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/UserProfileRequest'),
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(ref: '#/components/schemas/User'),
            ),
        ],
    )]
    public function updateProfile(UserProfileRequest $request): JsonResource
    {
        $currentUser = DPAuth::appUser();

        $validatedRequest = $request->validated();
        foreach ($validatedRequest as $key => $value) {
            if ($key === 'phoneNumber') {
                // Since phone number is used as identifier, it will not be updated
                continue;
            }

            if ($key === 'delegatePhone') {
                $value = PhoneNumber::canonicalize(type($value)->asString());
                if (!$this->canDelegate($currentUser, $value)) {
                    abort(422, 'Not eligible to delegate');
                }
            }

            $currentUser->$key = $value;
        }

        $currentUser->save();

        return new UserResource($currentUser);
    }

    #[OA\Post(
        path: '/api/app/users/secret-key',
        tags: ['Users'],
        summary: 'Generate Secret Key for TOTP',
        operationId: 'generateSecretKey',
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(ref: '#/components/schemas/User'),
            ),
        ],
    )]
    public function generateSecretKey(): JsonResource
    {
        $currentUser = DPAuth::appUser();
        if ($currentUser->secretKey) {
            return new UserResource($currentUser);
        }

        $secretKey = User::generateSecretKey();
        $currentUser->secretKey = $secretKey;
        $currentUser->save();
        return new UserResource($currentUser);
    }

    #[OA\Delete(
        path: '/api/app/users/secret-key',
        tags: ['Users'],
        summary: 'Delete Secret Key for TOTP',
        operationId: 'deleteSecretKey',
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(ref: '#/components/schemas/User'),
            ),
        ],
    )]
    public function deleteSecretKey(): JsonResource
    {
        $currentUser = DPAuth::appUser();
        $currentUser->secretKey = null;
        $currentUser->save();
        return new UserResource($currentUser);
    }


    private function canDelegate(User $principal, string $delegatePhone): bool
    {
        if ($principal->isDelegateEligible) {
            return false;
        }

        return User::where('phoneNumber', $delegatePhone)
            ->where('isDelegateEligible', true)
            ->exists();
    }
}
