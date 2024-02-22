<?php

namespace Tests\Feature;

use Tests\TestCase;

class IndexTest extends TestCase
{
    /**
     * Tests index page.
     */
    public function test_index_page_can_be_rendered(): void
    {
        $response = $this->get('/admin');

        $response->assertStatus(200);
    }
}
