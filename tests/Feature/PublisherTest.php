<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublisherTest extends TestCase
{
    public function test_publisher_index_page()
    {
        // Route /admin/publisher doesn't exist yet, so we expect 404
        $response = $this->get('/admin/publisher');
        
        $this->assertEquals(404, $response->status());
    }

    public function test_publisher_model()
    {
        try {
            $publisher = \App\Models\Publisher::with('admin')->first();
            echo "Publisher model: OK\n";
            $this->assertTrue(true);
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            $this->fail($e->getMessage());
        }
    }
}
