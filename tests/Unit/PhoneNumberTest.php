<?php

namespace Tests\Unit;

use App\Helpers\PhoneNumber;
use Tests\TestCase;

class PhoneNumberTest extends TestCase
{
    public function testCanonicalize() {
        $this->assertEquals('+6281234', PhoneNumber::canonicalize('081234'));
        $this->assertEquals('+1400500', PhoneNumber::canonicalize('1-400-500'));
        $this->assertEquals('+6212345', PhoneNumber::canonicalize('+6212 345'));
        $this->assertEquals('+1800700', PhoneNumber::canonicalize('+1 800_700'));
        $this->assertEquals('+6281234', PhoneNumber::canonicalize('81234'));
    }
}
