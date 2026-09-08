<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\User;

class InstallController extends Controller
{
    /**
     * Step 1: Requirements Check
     */
    public function index()
    {
        $requirements = [
            'php' => version_compare(PHP_VERSION, '8.2.0', '>='),
            'extensions' => [
                'bcmath'    => extension_loaded('bcmath'),
                'ctype'     => extension_loaded('ctype'),
                'fileinfo'  => extension_loaded('fileinfo'),
                'mbstring'  => extension_loaded('mbstring'),
                'openssl'   => extension_loaded('openssl'),
                'pdo_pgsql' => extension_loaded('pdo_pgsql'),
                'xml'       => extension_loaded('xml'),
            ],
            'permissions' => [
                '.env'             => is_writable(base_path('.env')),
                'storage'          => is_writable(storage_path()),
                'bootstrap/cache'  => is_writable(base_path('bootstrap/cache')),
            ],
        ];

        $allChecksPass = $requirements['php'];
        foreach ($requirements['extensions'] as $pass) {
            if (!$pass) {
                $allChecksPass = false;
            }
        }
        
        foreach ($requirements['permissions'] as $pass) {
            if (!$pass) {
                $allChecksPass = false;
            }
        }

        $requirements['allPass'] = $allChecksPass;

        return view('install.step1', compact('requirements'));
    }

    /**
     * Step 2: Database Setup
     */
    public function step2()
    {
        return view('install.step2');
    }

    public function testDatabase(Request $request)
    {
        $request->validate([
            'db_driver' => 'required|in:pgsql,mysql,sqlite',
            'db_host' => 'required_unless:db_driver,sqlite',
            'db_port' => 'required_unless:db_driver,sqlite',
            'db_database' => 'required',
            'db_username' => 'required_unless:db_driver,sqlite',
            'db_password' => 'nullable',
        ]);

        try {
            if ($request->db_driver === 'sqlite') {
                $dbPath = base_path('database/database.sqlite');
                if (!file_exists($dbPath)) {
                    touch($dbPath);
                }
                config()->set('database.connections.sqlite_test', [
                    'driver' => 'sqlite',
                    'url' => env('DATABASE_URL'),
                    'database' => $dbPath,
                    'prefix' => '',
                    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
                ]);
                DB::purge('sqlite_test');
                DB::connection('sqlite_test')->getPdo();
            } else {
                config()->set('database.connections.dynamic_test', [
                    'driver' => $request->db_driver,
                    'url' => env('DATABASE_URL'),
                    'host' => $request->db_host,
                    'port' => $request->db_port,
                    'database' => $request->db_database,
                    'username' => $request->db_username,
                    'password' => $request->db_password,
                    'charset' => 'utf8',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'search_path' => 'public',
                    'sslmode' => 'prefer',
                ]);

                DB::purge('dynamic_test');
                DB::connection('dynamic_test')->getPdo();
            }

            // Cache credentials in session for final step
            session([
                'install_db' => $request->only('db_driver', 'db_host', 'db_port', 'db_database', 'db_username', 'db_password')
            ]);

            return response()->json(['success' => true, 'message' => 'Database connection successful!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Step 3: SMTP Mail Configuration
     */
    public function step3()
    {
        return view('install.step3');
    }

    public function testSmtp(Request $request)
    {
        $request->validate([
            'mail_host' => 'required',
            'mail_port' => 'required',
            'mail_username' => 'required',
            'mail_password' => 'required',
            'mail_encryption' => 'nullable',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required',
        ]);

        try {
            config()->set('mail.mailers.smtp_test', [
                'transport' => 'smtp',
                'url' => env('MAIL_URL'),
                'host' => $request->mail_host,
                'port' => $request->mail_port,
                'encryption' => $request->mail_encryption,
                'username' => $request->mail_username,
                'password' => $request->mail_password,
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
            ]);

            config()->set('mail.from.address', $request->mail_from_address);
            config()->set('mail.from.name', $request->mail_from_name);
            
            config()->set('mail.default', 'smtp_test');

            Mail::raw('This is a test email from the IAMJOS installer.', function ($message) use ($request) {
                $message->to($request->mail_from_address)
                        ->subject('IAMJOS SMTP Test');
            });

            // Cache credentials
            session([
                'install_mail' => $request->only(
                    'mail_host', 'mail_port', 'mail_username', 'mail_password', 
                    'mail_encryption', 'mail_from_address', 'mail_from_name'
                )
            ]);

            return response()->json(['success' => true, 'message' => 'Test email sent successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'SMTP Test failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Step 4: Admin & Site Setup
     */
    public function step4()
    {
        return view('install.step4');
    }

    public function execute(Request $request)
    {
        $request->validate([
            'admin_name' => 'required',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:8|confirmed',
            'app_url' => 'required|url',
        ]);
        
        if (file_exists(base_path('.env')) && !is_writable(base_path('.env'))) {
            return back()->with('error', 'Instalasi Gagal: File .env bersifat Read-Only. Mohon jalankan "chown -R www:www ' . base_path() . '" di server Anda agar web server dapat menulis konfigurasi.');
        }

        try {
            $dbConfig = session('install_db');
            $mailConfig = session('install_mail');

            if (!$mailConfig) {
                $mailConfig = [
                    'mail_host' => '127.0.0.1',
                    'mail_port' => '2525',
                    'mail_username' => '',
                    'mail_password' => '',
                    'mail_encryption' => '',
                    'mail_from_address' => 'hello@example.com',
                    'mail_from_name' => 'IAMJOS System',
                ];
            }

            if (!$dbConfig) {
                return back()->with('error', 'Database Session expired. Please restart the installer.');
            }

            // 1. Re-configure DB dynamically in-memory to run migrations
            $driver = $dbConfig['db_driver'] ?? 'pgsql';
            if ($driver === 'sqlite') {
                config()->set('database.connections.sqlite.database', base_path('database/database.sqlite'));
            } else {
                config()->set("database.connections.{$driver}.host", $dbConfig['db_host']);
                config()->set("database.connections.{$driver}.port", $dbConfig['db_port']);
                config()->set("database.connections.{$driver}.database", $dbConfig['db_database']);
                config()->set("database.connections.{$driver}.username", $dbConfig['db_username']);
                config()->set("database.connections.{$driver}.password", $dbConfig['db_password']);
            }
            config()->set('database.default', $driver);
            DB::purge($driver);

            // 2. Setup Super Admin credentials for the Seeder (Avoid putenv because aaPanel disables it)
            $_ENV['SUPER_ADMIN_EMAIL'] = $request->admin_email;
            $_SERVER['SUPER_ADMIN_EMAIL'] = $request->admin_email;
            
            $_ENV['SUPER_ADMIN_NAME'] = $request->admin_name;
            $_SERVER['SUPER_ADMIN_NAME'] = $request->admin_name;
            
            $_ENV['SUPER_ADMIN_PASSWORD'] = $request->admin_password;
            $_SERVER['SUPER_ADMIN_PASSWORD'] = $request->admin_password;

            // 3. Migrate and Seed (using the dynamic connection config)
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);

            // Ensure the admin user exists (fallback if seeder failed for any reason)
            $admin = User::firstOrCreate(
                ['email' => $request->admin_email],
                [
                    'name' => $request->admin_name,
                    'username' => strstr($request->admin_email, '@', true) ?: 'admin',
                    'password' => Hash::make($request->admin_password),
                    'email_verified_at' => now(),
                ]
            );

            // Sync Mail Settings to Database (overriding seeder defaults)
            if (class_exists(\App\Models\SystemSetting::class)) {
                \App\Models\SystemSetting::where('key', 'mail_host')->update(['value' => $mailConfig['mail_host']]);
                \App\Models\SystemSetting::where('key', 'mail_port')->update(['value' => $mailConfig['mail_port']]);
                \App\Models\SystemSetting::where('key', 'mail_username')->update(['value' => $mailConfig['mail_username'] ?? '']);
                \App\Models\SystemSetting::where('key', 'mail_password')->update(['value' => $mailConfig['mail_password'] ?? '']);
                \App\Models\SystemSetting::where('key', 'mail_encryption')->update(['value' => $mailConfig['mail_encryption'] ?? '']);
                \App\Models\SystemSetting::where('key', 'mail_from_address')->update(['value' => $mailConfig['mail_from_address']]);
                \App\Models\SystemSetting::where('key', 'mail_from_name')->update(['value' => $mailConfig['mail_from_name']]);
            }

            // 4. Create storage/installed file (so application knows it is installed)
            File::put(storage_path('installed'), 'installed_at: ' . now());
            File::put(storage_path('install.log'), 'installed_at: ' . now() . "\n" . Artisan::output());

            $driver = $dbConfig['db_driver'] ?? 'pgsql';
            
            // 5. Update .env (DO THIS LAST to prevent php artisan serve from killing the request process mid-way!)
            $envUpdates = [
                'APP_URL' => $request->app_url,
                'DB_CONNECTION' => $driver,
                'DB_HOST' => $dbConfig['db_host'] ?? '127.0.0.1',
                'DB_PORT' => $dbConfig['db_port'] ?? '',
                'DB_DATABASE' => $driver === 'sqlite' ? base_path('database/database.sqlite') : $dbConfig['db_database'],
                'DB_USERNAME' => $dbConfig['db_username'] ?? '',
                'DB_PASSWORD' => $dbConfig['db_password'] ?? '',
                'MAIL_HOST' => $mailConfig['mail_host'],
                'MAIL_PORT' => $mailConfig['mail_port'],
                'MAIL_USERNAME' => $mailConfig['mail_username'],
                'MAIL_PASSWORD' => $mailConfig['mail_password'],
                'MAIL_ENCRYPTION' => $mailConfig['mail_encryption'] ?? '',
                'MAIL_FROM_ADDRESS' => $mailConfig['mail_from_address'],
                'MAIL_FROM_NAME' => '"' . $mailConfig['mail_from_name'] . '"',
            ];
            $this->updateEnvFile($envUpdates);

            // Clear cache
            Artisan::call('config:clear');
            Artisan::call('cache:clear');
            
            // Clean up session
            session()->forget(['install_db', 'install_mail']);

            return redirect('/login')->with('success', 'Installation successful! Please log in.');
        } catch (\Exception $e) {
            return back()->with('error', 'Installation failed: ' . $e->getMessage());
        }
    }

    private function updateEnvFile(array $data)
    {
        $envFile = base_path('.env');
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            
            foreach ($data as $key => $value) {
                // Make sure value is scalar
                $value = (string) $value;

                $pattern = "/^{$key}=.*/m";
                $replacement = "{$key}={$value}";

                if (preg_match($pattern, $content)) {
                    $content = preg_replace($pattern, $replacement, $content);
                } else {
                    $content .= "\n{$replacement}";
                }
            }
            
            file_put_contents($envFile, $content);
        }
    }
}
