<?php

namespace Tests\Feature;

use Tests\TestCase;

class IndexTest extends TestCase
{
    /**
     * Tests index page.
     */
    public function test_index_page_should_return_smiley(): void
    {
        $response = $this->get('/');

        $response
            ->assertStatus(200)
            ->assertSee(':D');
    }
}
