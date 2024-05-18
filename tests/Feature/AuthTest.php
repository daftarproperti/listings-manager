<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Http\Services\WhatsAppService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;

class AuthTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Config::set('services.whatsapp.base_url', 'https://fake-url.com/messages');
        Config::set('services.whatsapp.secret', 'fake-secret');
        Config::set('services.whatsapp.phone_number_id', '2912039102930');

        User::truncate();
    }

    public function testSendOTP()
    {
        $phoneNumber = '081210002000';

        $this->mock(WhatsAppService::class, function(MockInterface $mock) {
            $mock->shouldReceive('sendOTP')->once()->andReturn(true);
        });

        $response = $this->postJson('/api/auth/send-otp', [
            'phoneNumber' => $phoneNumber,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'timestamp']);
    }

    public function testVerifyOTPWithValidToken()
    {
        $phoneNumber = '081210112011';
        $otpCode = '123456';
        $timestamp = time();
        $salt = config('app.key');
        $token = Hash::make($otpCode . $timestamp . $salt);

        $response = $this->postJson('/api/auth/verify-otp', [
            'phoneNumber' => $phoneNumber,
            'token' => $token,
            'timestamp' => $timestamp,
            'otpCode' => $otpCode,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['accessToken', 'success', 'user'])
                 ->assertJson(['success' => true]);
    }

    public function testVerifyOTPWithInvalidToken()
    {
        $phoneNumber = '081210012001';
        $otpCode = '133144';
        $timestamp = time();
        $invalidToken = 'invalidtoken';

        $response = $this->postJson('/api/auth/verify-otp', [
            'phoneNumber' => $phoneNumber,
            'token' => $invalidToken,
            'timestamp' => $timestamp,
            'otpCode' => $otpCode,
        ]);

        $response->assertStatus(401)
                 ->assertJson(['success' => false]);
    }

    public function testVerifyOTPWithExpiredTimestamp()
    {
        $phoneNumber = '081210022002';
        $otpCode = '244411';
        $timestamp = time();
        $salt = config('app.key');
        $token = Hash::make($otpCode . $timestamp . $salt);
        $timestamp = time() + 12000;

        $response = $this->postJson('/api/auth/verify-otp', [
            'phoneNumber' => $phoneNumber,
            'token' => $token,
            'timestamp' => $timestamp,
            'otpCode' => $otpCode,
        ]);

        $response->assertStatus(401)
                 ->assertJson(['success' => false]);
    }
}
