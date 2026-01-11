<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublisherTest extends TestCase
{
    public function test_publisher_index_page()
    {
        $response = $this->get('/admin/publisher');
        
        if ($response->status() !== 200 && $response->status() !== 302) {
            echo "Status: " . $response->status() . "\n";
            echo "Error: " . $response->getContent() . "\n";
        }
        
        $this->assertIn($response->status(), [200, 302]);
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
