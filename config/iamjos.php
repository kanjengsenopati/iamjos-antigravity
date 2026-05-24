<?php
return [
    'instance_id'           => env('IAMJOS_INSTANCE_ID', 'iamjos-instance-1'),
    'license_key'           => env('IAMJOS_LICENSE_KEY'),
    'license_check_enabled' => (bool) env('IAMJOS_LICENSE_CHECK_ENABLED', false),
    // KAMPUS = Kantor Manajemen Pusat IamJOS
    // URL server KAMPUS yang mengelola lisensi semua instance IAMJOS
    'kampus_url'            => env('IAMJOS_KAMPUS_URL'),
];
