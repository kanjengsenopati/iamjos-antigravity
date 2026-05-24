<?php

namespace App\Services\HealthCheck;

interface HealthCheckerInterface
{
    public function check(): CheckResult;
}
