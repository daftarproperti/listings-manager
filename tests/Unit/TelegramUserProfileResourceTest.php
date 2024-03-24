<?php

namespace Tests\Unit;

use App\Models\Resources\TelegramUserProfileResource;
use App\Models\TelegramUser;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;


class TelegramUserProfileResourceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Config::set('services.google.bucket_name', 'the-bucket');
    }

    public function test_with_correct_profile_field(): void
    {
        $profile = [
            'profile' => [
                'id' => 123,
                'name' => 'John No',
                'city' => 'Moscow',
                'description' => 'some description',
                'company' => 'some company',
                'picture' => 'some-picture.jpg',
                'phoneNumber' => '333333333',
                'isPublicProfile' => true,
            ]
        ];

        $telegramUser = TelegramUser::factory()->create([
            'user_id' => 123,
            'first_name' => 'John',
            'last_name' => 'No',
            'username' => 'johno',
        ] + $profile);


        $response = (new TelegramUserProfileResource($telegramUser))->resolve();

        $expectedProfile = [
            'publicId' => $response['publicId'],
            'id' => 123,
            'name' => 'John No',
            'city' => 'Moscow',
            'description' => 'some description',
            'company' => 'some company',
            'picture' => 'https://storage.googleapis.com/the-bucket/some-picture.jpg',
            'phoneNumber' => '333333333',
            'isPublicProfile' => true,
        ];
        $this->assertEquals($expectedProfile, $response);
    }

    public function test_with_wrong_phone_number_profile_field(): void
    {
        $profile = [
            'profile' => [
                'id' => 123,
                'name' => 'John No',
                'city' => 'Moscow',
                'description' => 'some description',
                'company' => 'some company',
                'picture' => 'some picture',
                'phone_number' => '333333333',
                'isPublicProfile' => true,
            ]
        ];

        $telegramUser = TelegramUser::factory()->create([
            'user_id' => 123,
            'first_name' => 'John',
            'last_name' => 'No',
            'username' => 'johno',
        ] + $profile);


        $response = (new TelegramUserProfileResource($telegramUser))->resolve();

        $this->assertNotEquals($profile['profile'], $response);
        $this->assertNull($response['phoneNumber']);
    }
}
