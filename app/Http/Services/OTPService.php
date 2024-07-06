<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OTPService
{
    private string $method;

    // WhatsApp Config
    private string $waAPIUrl;
    private string $waAPISecret;
    private string $waPhoneNumberId;

    // Twilio Config
    private string $twilioAPIURL;
    private string $twilioAuthToken;
    private string $twilioAccountSID;
    private string $twilioPhoneNumber;

    public function __construct()
    {
        $this->method = type(config('services.otp_method'))->asString();

        $this->waAPIUrl = type(config('services.whatsapp.base_url'))->asString();
        $this->waAPISecret = type(config('services.whatsapp.secret'))->asString();
        $this->waPhoneNumberId = type(config('services.whatsapp.phone_number_id'))->asString();

        $this->twilioAPIURL = type(config('services.twilio.base_url'))->asString();
        $this->twilioAuthToken = type(config('services.twilio.auth_token'))->asString();
        $this->twilioAccountSID = type(config('services.twilio.account_sid'))->asString();
        $this->twilioPhoneNumber = type(config('services.twilio.phone_number'))->asString();
    }

    public function sendOTP(string $phoneNumber, string $otpCode): bool
    {
        $phoneNumber = $this->canonicalizePhoneNumber($phoneNumber);

        if (App::environment('local', 'development')) {
            Log::info('OTP Code: '.$otpCode.' sent to: '.$phoneNumber);
        }

        switch ($this->method) {
            case 'TWILIO':
                return $this->sendTwilioOTP($phoneNumber, $otpCode);
            case 'WHATSAPP':
                return $this->sendWAOTP($phoneNumber, $otpCode);
            case 'LOCAL':
                return true;
            default:
                throw new \InvalidArgumentException('Invalid OTP sending method.');
        }
    }

    private function sendWAOTP(string $phoneNumber, string $otpCode): bool
    {
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
            'Authorization' => 'Bearer ' . $this->waAPISecret,
            'Content-Type' => 'application/json',
        ])
            ->post($this->waAPIUrl . $this->waPhoneNumberId . '/messages', $data);

        if (!$response->successful()) {
            throw new \ErrorException($response->body());
        }

        return true;
    }

    private function sendTwilioOTP(string $phoneNumber, string $otpCode): bool
    {
        $response = Http::withBasicAuth($this->twilioAccountSID, $this->twilioAuthToken)
            ->asForm()
            ->post($this->twilioAPIURL . $this->twilioAccountSID . '/Messages.json', [
                'To' => $phoneNumber,
                'From' => $this->twilioPhoneNumber,
                'Body' => 'Your OTP code is '.$otpCode.'. Do not share this code with anyone.',
            ]);

        if (!$response->successful()) {
            throw new \ErrorException($response->body());
        }

        return true;
    }

    public function canonicalizePhoneNumber(string $phoneNumber): string
    {
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);;

        if (!$phoneNumber) return '';

        if (str_starts_with($phoneNumber, '8')) {
            $phoneNumber = '62' . $phoneNumber;
        }

        if (str_starts_with($phoneNumber, '0')) {
            $phoneNumber = '62' . substr($phoneNumber, 1);
        }

        return '+' . $phoneNumber;
    }
}
