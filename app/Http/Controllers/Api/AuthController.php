<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Assert;
use App\Http\Controllers\Controller;
use App\Http\Services\WhatsAppService;
use App\Rules\IndonesiaPhoneFormat;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected string $salt;

    public function __construct()
    {
        $this->salt = Assert::string(config('app.key'));
    }

    /**
     * @OA\Post(
     *     path="/api/auth/send-otp",
     *     tags={"Auth"},
     *     summary="Send OTP",
     *     operationId="auth.send_otp",
     *     @OA\Parameter(
     *         name="phoneNumber",
     *         in="path",
     *         required=true,
     *         description="Phone Number",
     *         @OA\Schema(
     *             type="string"
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                  property="token",
     *                  type="string",
     *                  description="JWT Token used for authentication"
     *             ),
     *             @OA\Property(
     *                  property="timestamp",
     *                  type="integer",
     *                  format="int64",
     *                  description="Timestamp of when the OTP was created"
     *             )
     *         ),
     *     )
     * )
     */
    public function sendOTP(Request $request): JsonResponse
    {
        $validatedRequest = $request->validate([
            'phoneNumber' => 'required', 'string', new IndonesiaPhoneFormat
        ]);
        $phoneNumber = $validatedRequest['phoneNumber'];

        $otpCode = sprintf("%06d", random_int(0, 999999));
        $whatsappService = new WhatsAppService();

        $whatsappService->sendOTP($phoneNumber, $otpCode);

        $timestamp = time();
        $token = Hash::make($otpCode . $timestamp . $this->salt);

        return response()->json([
            'token' => $token,
            'timestamp' => $timestamp
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/auth/verify-otp",
     *     tags={"Auth"},
     *     summary="Verify OTP",
     *     operationId="auth.verify_otp",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"token", "timestamp", "otpCode"},
     *                 @OA\Property(
     *                     property="token",
     *                     type="string",
     *                     description="Token to verify"
     *                 ),
     *                 @OA\Property(
     *                     property="timestamp",
     *                     type="integer",
     *                     format="int64",
     *                     description="Timestamp of when the OTP was created"
     *                 ),
     *                 @OA\Property(
     *                     property="otpCode",
     *                     type="string",
     *                     description="User's OTP Code"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true,
     *                 description="Verify status"
     *             )
     *         ),
     *     )
     * )
     */
    public function verifyOTP(Request $request): JsonResponse
    {
        $validatedRequest = $request->validate([
            'token' => ['required', 'string'],
            'timestamp' => ['required', 'numeric'],
            'otpCode' => ['required', 'string']
        ]);
        $token = $validatedRequest['token'];
        $timestamp = $validatedRequest['timestamp'];
        $otpCode = $validatedRequest['otpCode'];

        if (Hash::check($otpCode . $timestamp . $this->salt, $token) && (time() - $timestamp < 120)) {
            return response()->json([
                'success' => true
            ]);
        }
        
        return response()->json(['success' => false], 401);
    }
}
