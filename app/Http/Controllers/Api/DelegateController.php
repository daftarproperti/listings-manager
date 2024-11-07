<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DPAuth;
use App\Helpers\PhoneNumber;
use App\Http\Controllers\Controller;
use App\Models\Resources\UserResource;
use App\Models\User;
use App\Rules\IndonesiaPhoneFormat;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DelegateController extends Controller
{
    #[OA\Get(
        path: '/api/app/delegates/user/{phoneNumber}',
        tags: ['Delegates'],
        summary: 'Get isDelegateEligible user by phone number',
        operationId: 'delegates.getUserByPhoneNumber',
        parameters: [
            new OA\Parameter(
                name: 'phoneNumber',
                in: 'path',
                required: true,
                description: 'User Phone Number',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'success',
                content: new OA\JsonContent(ref: '#/components/schemas/User'),
            ),
            new OA\Response(
                response: 404,
                description: 'User not found',
            ),
        ],
    )]
    public function getUserByPhoneNumber(string $phoneNumber): JsonResource
    {
        $request = new Request(['phoneNumber' => $phoneNumber]);

        // Validate the phone number using the rules
        $request->validate([
            'phoneNumber' => ['required', 'string', new IndonesiaPhoneFormat()],
        ]);

        $phoneNumber = PhoneNumber::canonicalize($phoneNumber);

        $currentUser = DPAuth::appUser();

        if ($currentUser->isDelegateEligible) {
            abort(422, 'Not eligible to delegate');
        }

        $user = User::where('phoneNumber', $phoneNumber)
            ->where('isDelegateEligible', true)
            ->first();

        if (!$user) {
            abort(404, 'User not found');
        }

        return new UserResource($user);
    }
}
