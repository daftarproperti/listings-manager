<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Http\Services\OTPService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;

class AuthTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Config::set('services.otp_method', 'TWILIO');

        Config::set('services.whatsapp.base_url', 'https://fake-url.com/messages');
        Config::set('services.whatsapp.secret', 'fake-secret');
        Config::set('services.whatsapp.phone_number_id', '2912039102930');

        Config::set('services.twilio.base_url', 'https://fake-url.com/messages');
        Config::set('services.twilio.auth_token', 'fake-secret');
        Config::set('services.twilio.account_sid', '291203910293011');
        Config::set('services.twilio.phone_number', '2912039102930');

        Config::set('services.root_users', ['+6281211112222']);

        User::truncate();

        putenv('USER_ID_KEY=some-secret-key');
    }

    public function testSendOTP()
    {
        $phoneNumber = '081210002000';

        $this->mock(OTPService::class, function(MockInterface $mock) {
            $mock->shouldReceive('sendOTP')->once()->andReturn(true);
            $mock->shouldReceive('canonicalizePhoneNumber')->once()->andReturn('+6281210002000');
        });

        Carbon::setTestNow(Carbon::create(2024, 05, 20, 10, 00));
        $response = $this->postJson('/api/auth/send-otp', [
            'phoneNumber' => $phoneNumber,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'timestamp']);
    }

    public function testVerifyOTPWithValidToken()
    {
        $phoneNumber = '+6281210112011';
        $otpCode = '123456';
        $time = Carbon::create(2024, 05, 20, 10, 01);
        Carbon::setTestNow($time);

        $timestamp = $time->timestamp;
        $salt = config('app.key');
        $token = Hash::make($phoneNumber . $otpCode . $timestamp . $salt);

        $response = $this->postJson('/api/auth/verify-otp', [
            'phoneNumber' => $phoneNumber,
            'token' => $token,
            'timestamp' => $timestamp,
            'otpCode' => $otpCode,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['accessToken', 'success', 'user'])
                 ->assertJson(['success' => true]);

        $json = $response->json();

        $this->assertEquals(43453923302522360, $json['user']['user_id']);

        $this->assertDatabaseHas('users', [
            'user_id' => $json['user']['user_id'],
            'phoneNumber' => '+6281210112011',
        ]);
    }

    public function testVerifyOTPWithInvalidToken()
    {
        $phoneNumber = '+6281210012001';
        $otpCode = '133144';
        $time = Carbon::create(2024, 05, 20, 10, 01);
        Carbon::setTestNow($time);

        $timestamp = $time->timestamp;
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
        $phoneNumber = '+6281210022002';
        $otpCode = '244411';
        $time = Carbon::create(2024, 05, 20, 10, 01);
        Carbon::setTestNow($time);

        $timestamp = $time->timestamp;
        $salt = config('app.key');
        $token = Hash::make($phoneNumber . $otpCode . $timestamp . $salt);
        $timestamp = $timestamp + 25000;

        $response = $this->postJson('/api/auth/verify-otp', [
            'phoneNumber' => $phoneNumber,
            'token' => $token,
            'timestamp' => $timestamp,
            'otpCode' => $otpCode,
        ]);

        $response->assertStatus(401)
                 ->assertJson(['success' => false]);
    }

    public function testVerifyOTPWithInvalidOTP()
    {
        $phoneNumber = '+6281210022002';
        $otpCode = '244411';
        $time = Carbon::create(2024, 05, 20, 10, 01);
        Carbon::setTestNow($time);

        $timestamp = $time->timestamp;
        $salt = config('app.key');
        $token = Hash::make($phoneNumber . $otpCode . $timestamp . $salt);
        $otpCode = '120101';

        $response = $this->postJson('/api/auth/verify-otp', [
            'phoneNumber' => $phoneNumber,
            'token' => $token,
            'timestamp' => $timestamp,
            'otpCode' => $otpCode,
        ]);

        $response->assertStatus(401)
                 ->assertJson(['success' => false]);
    }

    public function testLogout()
    {
        $phoneNumber = '+6281210112011';
        $otpCode = '123456';
        $time = Carbon::create(2024, 05, 20, 10, 01);
        Carbon::setTestNow($time);

        $timestamp = $time->timestamp;
        $salt = config('app.key');
        $token = Hash::make($phoneNumber . $otpCode . $timestamp . $salt);

        $response = $this->postJson('/api/auth/verify-otp', [
            'phoneNumber' => $phoneNumber,
            'token' => $token,
            'timestamp' => $timestamp,
            'otpCode' => $otpCode,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['accessToken', 'success', 'user'])
                 ->assertJson(['success' => true]);

        $json = $response->json();
        $accessToken = $json['accessToken'];

        $this->assertEquals(43453923302522360, $json['user']['user_id']);

        $this->assertDatabaseHas('users', [
            'user_id' => $json['user']['user_id'],
            'phoneNumber' => '+6281210112011',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success'])
                 ->assertJson(['success' => true]);

        // If we hit logout or any other endpoints again with the revoked token, it now should return unauthorized
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(403)
                 ->assertJsonStructure(['error'])
                 ->assertJson(['error' => 'Unauthorized']);
    }

    public function testImpersonateWithRootUser() {
        $phoneNumber = '+6281211112222';
        $otpCode = '123456';
        $time = Carbon::create(2024, 05, 20, 10, 01);
        Carbon::setTestNow($time);

        $timestamp = $time->timestamp;
        $salt = config('app.key');
        $token = Hash::make($phoneNumber . $otpCode . $timestamp . $salt);

        $response = $this->postJson('/api/auth/verify-otp', [
            'phoneNumber' => $phoneNumber,
            'token' => $token,
            'timestamp' => $timestamp,
            'otpCode' => $otpCode,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['accessToken', 'success', 'user'])
                 ->assertJson(['success' => true]);

        $json = $response->json();
        $accessToken = $json['accessToken'];

        User::create([
            'phoneNumber' => '+6281231234567',
            'name' => 'test impersonate person',
            'email' => 'test@impersonate.com',
            'description' => 'this is for impersonate purpose only'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->postJson('/api/auth/impersonate', [
            'phoneNumber' => '081231234567',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['accessToken', 'success', 'user'])
                 ->assertJson(['success' => true]);

        // For now allow impersonation in production since it's expected to have a lot of manual help for customers.
        $this->app['env'] = 'production';
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->postJson('/api/auth/impersonate', [
            'phoneNumber' => '081231234567',
        ]);
        $response->assertStatus(200)
                 ->assertJsonStructure(['accessToken', 'success', 'user'])
                 ->assertJson(['success' => true]);
    }

    public function testImpersonateWithNormalUser() {
        $phoneNumber = '+6281212341234';
        $otpCode = '123456';
        $time = Carbon::create(2024, 05, 20, 10, 01);
        Carbon::setTestNow($time);

        $timestamp = $time->timestamp;
        $salt = config('app.key');
        $token = Hash::make($phoneNumber . $otpCode . $timestamp . $salt);

        $response = $this->postJson('/api/auth/verify-otp', [
            'phoneNumber' => $phoneNumber,
            'token' => $token,
            'timestamp' => $timestamp,
            'otpCode' => $otpCode,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['accessToken', 'success', 'user'])
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'phoneNumber' => '+6281212341234',
        ]);

        $json = $response->json();
        $accessToken = $json['accessToken'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->postJson('/api/auth/impersonate', [
            'phoneNumber' => '081210112011',
        ]);

        $response->assertStatus(403)
                 ->assertJson(['error' => 'Unauthorized']);
    }

    public function testCanonicalizePhoneNumber() {
        $otpService = new OTPService();
        $this->assertEquals('+6281234', $otpService->canonicalizePhoneNumber('081234'));
        $this->assertEquals('+1400500', $otpService->canonicalizePhoneNumber('1-400-500'));
        $this->assertEquals('+6212345', $otpService->canonicalizePhoneNumber('+6212 345'));
        $this->assertEquals('+1800700', $otpService->canonicalizePhoneNumber('+1 800_700'));
    }
}
