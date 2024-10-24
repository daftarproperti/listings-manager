<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        User::truncate();
    }
    public function test_has_delegate_user(): void
    {
        $delegate = User::factory()->create([
            'phoneNumber' => '081239129321',
        ]);

        $principal =  User::factory()->create([
            'phoneNumber' => '081239129322',
            'delegatePhone' => $delegate->phoneNumber,
        ]);

        $delegateUser = $principal->delegateUser;
        $this->assertInstanceOf(User::class, $delegateUser);
        $this->assertEquals($principal->delegatePhone, $delegateUser->phoneNumber);
    }

    public function test_has_no_delegate_user(): void
    {
        $principal =  User::factory()->create([
            'phoneNumber' => '081239129322',
        ]);

        $this->assertNull($principal->delegateUser);
    }

    public function test_hash_phone_number_is_correct(): void
    {
        $user = User::factory()->create([
            'phoneNumber' => '081239129323',
        ]);

        $hashPhone = User::hashPhoneNumber($user->phoneNumber);
        $this->assertTrue(hash_equals(hash('sha256', $user->user_id.':'.$user->phoneNumber), $hashPhone));
    }
}
