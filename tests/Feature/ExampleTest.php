<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_example_feature_test(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}