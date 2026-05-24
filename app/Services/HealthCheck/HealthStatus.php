<?php

namespace App\Services\HealthCheck;

enum HealthStatus: string
{
    case Healthy   = 'healthy';   // semua komponen ok
    case Degraded  = 'degraded';  // komponen non-kritis error (storage/queue), tapi DB dan Redis ok
    case Unhealthy = 'unhealthy'; // komponen kritis error (DB atau Redis)
}
