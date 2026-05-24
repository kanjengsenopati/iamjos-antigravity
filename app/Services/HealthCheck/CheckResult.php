<?php

namespace App\Services\HealthCheck;

final class CheckResult
{
    public function __construct(
        public readonly string $status,      // "ok" | "error"
        public readonly ?string $message,    // null jika ok, pesan error jika error (TANPA kredensial)
        public readonly float $latencyMs,    // waktu eksekusi dalam milidetik
        public readonly array $metadata = [] // data tambahan (misal: last_processed_at untuk queue)
    ) {}

    /**
     * Factory method untuk hasil sukses.
     */
    public static function ok(float $latencyMs, array $metadata = []): self
    {
        return new self('ok', null, $latencyMs, $metadata);
    }

    /**
     * Factory method untuk hasil error.
     * Pesan error tidak boleh mengekspos kredensial atau informasi internal.
     */
    public static function error(string $message, float $latencyMs): self
    {
        return new self('error', $message, $latencyMs);
    }
}
