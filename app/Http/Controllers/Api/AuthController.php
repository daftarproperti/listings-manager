<?php

namespace App\Http\Controllers\Api;

use DateTime;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Services\OTPService;
use App\Http\Services\WhatsAppService;
use App\Models\Resources\UserResource;
use App\Models\User;
use App\Rules\IndonesiaPhoneFormat;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use App\Models\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    protected string $salt;
    protected OTPService $otpService;

    public function __construct(OTPService $otpService)
    {
        $this->salt = type(config('app.key'))->asString();
        $this->otpService = $otpService;
    }

    #[OA\Post(
        path: "/api/auth/send-otp",
        tags: ["Auth"],
        summary: "Send OTP",
        operationId: "auth.send_otp",
        parameters: [
            new OA\Parameter(
                name: "phoneNumber",
                in: "path",
                required: true,
                description: "Phone Number",
                schema: new OA\Schema(
                    type: "string"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "success",
                content: new OA\JsonContent(
                    type: "object",
                    properties: [
                        new OA\Property(
                            property: "token",
                            type: "string",
                            description: "JWT Token used for authentication"
                        ),
                        new OA\Property(
                            property: "timestamp",
                            type: "integer",
                            format: "int64",
                            description: "Timestamp of when the OTP was created"
                        )
                    ]
                )
            )
        ]
    )]
    public function sendOTP(Request $request): JsonResponse
    {
        $validatedRequest = $request->validate([
            'phoneNumber' => 'required', 'string', new IndonesiaPhoneFormat,
        ]);
        $phoneNumber = $validatedRequest['phoneNumber'];
        $phoneNumber = $this->otpService->canonicalizePhoneNumber($phoneNumber);

        $otpCode = sprintf("%06d", random_int(0, 999999));

        $this->otpService->sendOTP($phoneNumber, $otpCode);

        $timestamp = Carbon::now()->timestamp;
        $token = Hash::make($phoneNumber . $otpCode . $timestamp . $this->salt);

        return response()->json([
            'token' => $token,
            'timestamp' => $timestamp
        ]);
    }

    #[OA\Post(
        path: "/api/auth/verify-otp",
        tags: ["Auth"],
        summary: "Verify OTP",
        operationId: "auth.verify_otp",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["phoneNumber", "token", "timestamp", "otpCode"],
                properties: [
                    new OA\Property(property: "phoneNumber", type: "string", description: "User phone number"),
                    new OA\Property(property: "token", type: "string", description: "Token to verify"),
                    new OA\Property(property: "timestamp", type: "integer", format: "int64", description: "Timestamp of when the OTP was created"),
                    new OA\Property(property: "otpCode", type: "string", description: "User's OTP Code")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Success response",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true, description: "Verify status"),
                        new OA\Property(property: "accessToken", type: "string", example: "Akoasdk131o3ipIaskdlz", description: "Access token"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User", description: "User information")
                    ]
                )
            )
        ]
    )]
    public function verifyOTP(Request $request): JsonResponse
    {
        $validatedRequest = $request->validate([
            'phoneNumber' => 'required', 'string', new IndonesiaPhoneFormat,
            'token' => ['required', 'string'],
            'timestamp' => ['required', 'numeric'],
            'otpCode' => ['required', 'string']
        ]);

        $phoneNumber = $validatedRequest['phoneNumber'];
        $phoneNumber = $this->otpService->canonicalizePhoneNumber($phoneNumber);
        $token = $validatedRequest['token'];
        $timestamp = $validatedRequest['timestamp'];
        $otpCode = $validatedRequest['otpCode'];

        if (!(Hash::check($phoneNumber . $otpCode . $timestamp . $this->salt, $token) && (Carbon::now()->timestamp - $timestamp < 120))) {
            return response()->json([
                'success' => false
            ], 401);
        }

        $expiryDate = new DateTime();
        $expiryDate->modify('+1 month');

        /** @var User|null $user */
        $user = User::where('phoneNumber', $phoneNumber)->first();
        if (!$user) {
            $user = new User();
            $user->phoneNumber = $phoneNumber;
            $user->save();
        }

        $token = $user->createToken('loginToken', ['*'], $expiryDate)->plainTextToken;

        return response()->json([
            'success' => true,
            'accessToken' => $token,
            'user' => new UserResource($user)
        ]);
    }

    #[OA\Post(
        path: "/api/auth/logout",
        tags: ["Auth"],
        summary: "Logout",
        operationId: "auth.logout",
        security: [
            ["bearerAuth" => []]
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success response",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true, description: "Logout status")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Token not found response",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false, description: "Logout status"),
                    ]
                )
            )
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $token = $request->bearerToken();

        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                $accessToken->delete();
                return response()->json([
                    'success' => true
                ]);
            }
        }

        return response()->json([
            'success' => false
        ], 404);
    }

    #[OA\Post(
        path: "/api/auth/impersonate",
        tags: ["Auth"],
        summary: "Impersonate",
        operationId: "auth.impersonate",
        parameters: [
            new OA\Parameter(
                name: "phoneNumber",
                in: "path",
                required: true,
                description: "Phone Number",
                schema: new OA\Schema(
                    type: "string"
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success response",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true, description: "Verify status"),
                        new OA\Property(property: "accessToken", type: "string", example: "Akoasdk131o3ipIaskdlz", description: "Access token"),
                        new OA\Property(property: "user", ref: "#/components/schemas/User", description: "User information")
                    ]
                )
            )
        ]
    )]
    public function impersonate(Request $request): JsonResponse
    {
        $validatedRequest = $request->validate([
            'phoneNumber' => 'required', 'string', new IndonesiaPhoneFormat,
        ]);
        $phoneNumber = $validatedRequest['phoneNumber'];
        $phoneNumber = $this->otpService->canonicalizePhoneNumber($phoneNumber);

        $expiryDate = new DateTime();
        $expiryDate->modify('+3 hour');

        /** @var User|null $user */
        $user = User::where('phoneNumber', $phoneNumber)->first();
        if (!$user) {
            $user = new User();
            $user->phoneNumber = $phoneNumber;
            $user->save();
        }

        $token = $user->createToken('loginToken', ['*'], $expiryDate)->plainTextToken;

        return response()->json([
            'success' => true,
            'accessToken' => $token,
            'user' => new UserResource($user)
        ]);
    }
}
