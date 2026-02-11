<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_displays_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Smart Seha');
    }
}
