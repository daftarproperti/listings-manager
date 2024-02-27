<?php

namespace Tests\Feature;

use App\DTO\Telegram\Message;
use App\DTO\Telegram\Update;
use App\Models\BaseAttributeCaster;
use Tests\TestCase;

class CastTestBar extends BaseAttributeCaster {
    public string $title;
    public bool $check;
}

class CastTestFoo extends BaseAttributeCaster {
    public int $id;
    public string $name;
    public CastTestBar $bar;
}

class AttributeCasterTest extends TestCase
{
    /**
     * Basic conversion.
     */
    public function test_basic_conversion(): void
    {
        $expected = new CastTestFoo();
        $expected->id = '101';
        $expected->name = 'John';
        $expected->bar = new CastTestBar();
        $expected->bar->title = 'The Bar';
        $expected->bar->check = TRUE;

        $foo = CastTestFoo::from([
            'id' => 101,
            'name' => 'John',
            'bar' => [
                'title' => 'The Bar',
                'check' => TRUE,
            ],
            'non-existing-field' => 'something',
        ]);

        $this->assertEquals($expected, $foo);
        // assertEquals does not assert that the types are the same, so check again individually that the fields
        // have the same types not just values.
        $this->assertSame($expected->id, $foo->id);
        $this->assertSame($expected->name, $foo->name);
        $this->assertSame($expected->bar->title, $foo->bar->title);
        $this->assertSame($expected->bar->check, $foo->bar->check);
    }

    public function test_telegram_dto(): void
    {
        $update = Update::from([
            'update_id' => 2,
            'message' => [
                'message_id' => 5,
                'text' => 'Hello!',
                'unknown-should-be-ignored' => 1000,
            ],
            'unknown-field' => 'Hi',
        ]);

        $expected = new Update();
        $expected->update_id = 2;
        $expected->message = new Message();
        $expected->message->message_id = 5;
        $expected->message->text = 'Hello!';

        $this->assertEquals($expected, $update);
    }
}
