<?php

namespace Tests\Feature;

use Tests\TestCase;

class CsrfSessionTest extends TestCase
{
    /**
     * Test refresh-csrf endpoint returns a valid token JSON.
     */
    public function test_refresh_csrf_endpoint_returns_token(): void
    {
        $response = $this->get('/refresh-csrf');

        $response->assertStatus(200);
        $response->assertJsonStructure(['csrf_token']);
        $this->assertNotEmpty($response->json('csrf_token'));
    }

    /**
     * Test custom 419 error view exists.
     */
    public function test_419_error_view_exists(): void
    {
        $this->assertTrue(view()->exists('errors.419'));
    }
}
