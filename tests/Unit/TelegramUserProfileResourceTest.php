<?php

namespace Tests\Unit;

use App\Models\Resources\TelegramUserProfileResource;
use App\Models\TelegramUser;
use Tests\TestCase;


class TelegramUserProfileResourceTest extends TestCase
{
    public function test_with_correct_profile_field(): void
    {
        $profile = [
            'profile' => [
                'id' => 123,
                'name' => 'John No',
                'city' => 'Moscow',
                'description' => 'some description',
                'company' => 'some company',
                'picture' => 'some picture',
                'phoneNumber' => '333333333',
                'isPublicProfile' => true,
            ]
        ];

        $telegramUser = TelegramUser::factory()->create([
            'user_id' => 123,
            'first_name' => 'John',
            'last_name' => 'No',
            'username' => 'johno',
        ]+$profile);


        $response = (new TelegramUserProfileResource($telegramUser))->resolve();
        $profile['profile']['publicId'] = $response['publicId'];

        $this->assertEquals($profile['profile'], $response);
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
        ]+$profile);


        $response = (new TelegramUserProfileResource($telegramUser))->resolve();

        $this->assertNotEquals($profile['profile'], $response);
        $this->assertNull($response['phoneNumber']);
    }
}
