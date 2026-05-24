<?php

namespace App\Services\HealthCheck;

final class HealthReport
{
    public function __construct(
        public readonly HealthStatus $status,
        public readonly string $timestamp,    // ISO 8601 UTC
        public readonly string $version,
        public readonly int $uptimeSeconds,
        public readonly string $instanceId,
        public readonly array $checks,        // [component => CheckResult]
        public readonly array $metrics,       // [metric_name => value]
    ) {}

    /**
     * Konversi ke array untuk JSON response.
     * Struktur mengikuti format yang didefinisikan dalam design.md.
     */
    public function toArray(): array
    {
        $checksArray = [];

        foreach ($this->checks as $name => $result) {
            /** @var CheckResult $result */
            $checkData = [
                'status'     => $result->status,
                'message'    => $result->message,
                'latency_ms' => round($result->latencyMs, 2),
            ];

            // Tambahkan metadata jika ada (misal: last_processed_at untuk queue)
            foreach ($result->metadata as $key => $value) {
                $checkData[$key] = $value;
            }

            $checksArray[$name] = $checkData;
        }

        return [
            'status'          => $this->status->value,
            'timestamp'       => $this->timestamp,
            'version'         => $this->version,
            'uptime_seconds'  => $this->uptimeSeconds,
            'instance_id'     => $this->instanceId,
            'checks'          => $checksArray,
            'metrics'         => $this->metrics,
        ];
    }

    /**
     * HTTP status code yang sesuai dengan status kesehatan.
     * 200 jika Healthy, 503 jika Degraded atau Unhealthy.
     */
    public function httpStatus(): int
    {
        return $this->status === HealthStatus::Healthy ? 200 : 503;
    }
}
