<?php
namespace App\Enums;

enum LicenseStatus: string
{
    case Valid        = 'valid';
    case Invalid      = 'invalid';
    case Expired      = 'expired';
    case Unregistered = 'unregistered';
    case Unchecked    = 'unchecked';

    public function label(): string
    {
        return match ($this) {
            self::Valid        => 'Lisensi aktif dan valid',
            self::Invalid      => 'Lisensi tidak valid',
            self::Expired      => 'Lisensi telah kedaluwarsa',
            self::Unregistered => 'Instance belum terdaftar',
            self::Unchecked    => 'Status lisensi belum diperiksa',
        };
    }

    public function isOperational(): bool
    {
        return in_array($this, [self::Valid, self::Unchecked], true);
    }

    public static function fromString(string $value): self
    {
        return self::tryFrom(strtolower($value)) ?? self::Unchecked;
    }
}
