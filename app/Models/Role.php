<?php

namespace App\Models;

// Jika Anda menggunakan Laravel 10 atau lebih tinggi
use Illuminate\Database\Eloquent\Concerns\HasUuids;
// Kita akan memperluas (extend) model asli dari Spatie
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * CARA MODERN (LARAVEL 10+):
     * Cukup gunakan trait HasUuids ini. Laravel akan otomatis mengatur
     * $incrementing = false dan $keyType = 'string' untuk Anda.
     */
    use HasUuids;

    /**
     * CARA MANUAL (LARAVEL 9 KE BAWAH):
     * Jika Anda menggunakan versi Laravel lama, hapus baris "use HasUuids;" di atas
     * dan hapus tanda komentar dari dua baris di bawah ini.
     */
    const ROLE_ADMIN = 'Admin';
    const ROLE_PUBLISHER = 'Publisher';
    const ROLE_AUTHOR = 'Author';
    const ROLE_ASSESSOR = 'Assessor';
    public $incrementing = false;
    protected $keyType = 'string';
}
