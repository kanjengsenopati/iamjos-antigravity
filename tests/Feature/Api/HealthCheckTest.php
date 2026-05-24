<?php

/**
 * Test untuk Health Check API Endpoint
 *
 * Memverifikasi bahwa endpoint GET /api/v1/health:
 * - Dapat diakses tanpa autentikasi
 * - Mengembalikan struktur response yang benar
 * - Mengembalikan HTTP 200 saat semua komponen sehat
 * - Mengembalikan header Content-Type: application/json
 */

use App\Services\HealthCheck\CheckResult;
use App\Services\HealthCheck\DatabaseChecker;
use App\Services\HealthCheck\QueueChecker;
use App\Services\HealthCheck\RedisChecker;
use App\Services\HealthCheck\StorageChecker;

describe('Health Check API', function () {

    it('dapat diakses tanpa autentikasi', function () {
        $response = $this->getJson('/api/v1/health');

        // Harus mengembalikan 200 atau 503 — bukan 401 atau 403
        expect($response->status())->toBeIn([200, 503]);
    });

    it('mengembalikan header Content-Type application/json', function () {
        $response = $this->getJson('/api/v1/health');

        $response->assertHeader('Content-Type', 'application/json');
    });

    it('mengembalikan semua field wajib dalam response', function () {
        $response = $this->getJson('/api/v1/health');

        $response->assertJsonStructure([
            'status',
            'timestamp',
            'version',
            'uptime_seconds',
            'instance_id',
            'checks' => [
                'database' => ['status', 'latency_ms'],
                'redis'    => ['status', 'latency_ms'],
                'storage'  => ['status', 'latency_ms'],
                'queue'    => ['status', 'latency_ms'],
            ],
            'metrics' => [
                'active_journals',
                'pending_submissions',
            ],
        ]);
    });

    it('mengembalikan HTTP 200 saat semua komponen sehat', function () {
        // Mock semua checker agar mengembalikan "ok"
        $this->mock(DatabaseChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(CheckResult::ok(5.0))
        );
        $this->mock(RedisChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(CheckResult::ok(1.0))
        );
        $this->mock(StorageChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(CheckResult::ok(3.0))
        );
        $this->mock(QueueChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(CheckResult::ok(2.0))
        );

        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'healthy']);
    });

    it('mengembalikan HTTP 503 saat database tidak tersedia', function () {
        $this->mock(DatabaseChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(CheckResult::error('Database connection failed', 500.0))
        );
        $this->mock(RedisChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(CheckResult::ok(1.0))
        );
        $this->mock(StorageChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(CheckResult::ok(3.0))
        );
        $this->mock(QueueChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(CheckResult::ok(2.0))
        );

        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(503);
        $response->assertJson(['status' => 'unhealthy']);
    });

    it('mengembalikan status degraded saat hanya queue tidak aktif', function () {
        $this->mock(DatabaseChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(CheckResult::ok(5.0))
        );
        $this->mock(RedisChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(CheckResult::ok(1.0))
        );
        $this->mock(StorageChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(CheckResult::ok(3.0))
        );
        $this->mock(QueueChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andReturn(
                CheckResult::error('Queue worker may not be running', 2.0)
            )
        );

        $response = $this->getJson('/api/v1/health');

        // Degraded = 503 tapi status "degraded" bukan "unhealthy"
        $response->assertStatus(503);
        $response->assertJson(['status' => 'degraded']);
    });

    it('tidak mengekspos stack trace saat terjadi exception', function () {
        $this->mock(DatabaseChecker::class, fn($mock) =>
            $mock->shouldReceive('check')->andThrow(new \RuntimeException('Internal error with credentials'))
        );

        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(503);

        // Pastikan tidak ada stack trace atau pesan error internal
        $body = $response->getContent();
        expect($body)->not->toContain('RuntimeException');
        expect($body)->not->toContain('Internal error with credentials');
        expect($body)->not->toContain('trace');
    });

    it('mengembalikan status dan latency untuk setiap komponen', function () {
        $response = $this->getJson('/api/v1/health');

        $data = $response->json();

        foreach (['database', 'redis', 'storage', 'queue'] as $component) {
            expect($data['checks'][$component])->toHaveKey('status');
            expect($data['checks'][$component]['status'])->toBeIn(['ok', 'error']);
            expect($data['checks'][$component])->toHaveKey('latency_ms');
        }
    });

    it('metrics active_journals dan pending_submissions adalah integer non-negatif', function () {
        $response = $this->getJson('/api/v1/health');

        $data = $response->json();

        expect($data['metrics']['active_journals'])->toBeInt()->toBeGreaterThanOrEqual(0);
        expect($data['metrics']['pending_submissions'])->toBeInt()->toBeGreaterThanOrEqual(0);
    });

});
