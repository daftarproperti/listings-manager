<?php

namespace App\Http\Services;

use App\Helpers\Assert;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    private string $apiUrl;
    private string $apiSecret;
    private string $phoneNumberId;

    public function __construct()
    {
        $this->apiUrl = Assert::string(config('services.whatsapp.base_url'));
        $this->apiSecret = Assert::string(config('services.whatsapp.secret'));
        $this->phoneNumberId = Assert::string(config('services.whatsapp.phone_number_id'));
    }

    public function sendOTP(string $phoneNumber, string $otpCode): bool
    {
        $phoneNumber = $this->addCountryCode($phoneNumber);
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'template',
            'template' => [
                'name' => 'send_otp',
                'language' => [
                    'code' => 'id'
                ],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $otpCode
                            ]
                        ]
                    ],
                    [
                        'type' => 'button',
                        'sub_type' => 'url',
                        'index' => '0',
                        'parameters' => [
                            [
                                'type' => 'text',
                                'text' => $otpCode
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiSecret,
            'Content-Type' => 'application/json',
        ])
            ->post($this->apiUrl . $this->phoneNumberId . '/messages', $data);

        if (!$response->successful()) {
            throw new \ErrorException($response->body());
        }

        return true;
    }

    private function addCountryCode(string $phoneNumber): string
    {
        $idCountryCode = "62";
        if (substr($phoneNumber, 0, strlen($idCountryCode)) !== $idCountryCode) {
            return  $idCountryCode . substr($phoneNumber, 1);
        }

        return $phoneNumber;
    }
}
