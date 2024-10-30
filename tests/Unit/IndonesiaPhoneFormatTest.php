<?php

namespace Tests\Unit;

use App\Rules\IndonesiaPhoneFormat;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndonesiaPhoneFormatTest extends TestCase
{
    public function test_phone_number_is_invalid()
    {
        $validator = Validator::make(['phoneNumber' => '12345'], [
            'phoneNumber' => [new IndonesiaPhoneFormat()],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertEquals('Phone number is not a valid Indonesian phone number', $validator->errors()->first('phoneNumber'));
    }

    public function test_phone_number_is_valid_with_08_prefix()
    {
        $validator = Validator::make(['phoneNumber' => '08123456789'], [
            'phoneNumber' => [new IndonesiaPhoneFormat()],
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_phone_number_is_valid_with_62_prefix()
    {
        $validator = Validator::make(['phoneNumber' => '62123456789'], [
            'phoneNumber' => [new IndonesiaPhoneFormat()],
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_phone_number_is_valid_with_plus_62_prefix()
    {
        $validator = Validator::make(['phoneNumber' => '+62123456789'], [
            'phoneNumber' => [new IndonesiaPhoneFormat()],
        ]);

        $this->assertFalse($validator->fails());
    }
}
