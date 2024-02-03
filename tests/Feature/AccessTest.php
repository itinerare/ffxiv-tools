<?php

namespace Tests\Feature;

use Tests\TestCase;

class AccessTest extends TestCase {
    /**
     * Test getting the main page.
     */
    public function testGetIndex() {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
