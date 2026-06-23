<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Facades\Settings;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        return view('admin.system-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if ($setting) {
                Settings::setSystem($key, $value, $setting->type);
            }
        }

        return back()->with('success', 'System settings updated successfully.');
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $recipient = $request->input('email');

        try {
            // Re-override mail configurations dynamically to ensure they match what's currently in the DB
            $mailer = Settings::system('mail_mailer');
            if ($mailer) {
                config(['mail.default' => $mailer]);
                config(['mail.mailers.smtp.host' => Settings::system('mail_host', config('mail.mailers.smtp.host'))]);
                config(['mail.mailers.smtp.port' => (int) Settings::system('mail_port', config('mail.mailers.smtp.port'))]);
                config(['mail.mailers.smtp.username' => Settings::system('mail_username', config('mail.mailers.smtp.username'))]);
                config(['mail.mailers.smtp.password' => Settings::system('mail_password', config('mail.mailers.smtp.password'))]);
                
                $encryption = Settings::system('mail_encryption');
                if ($encryption === 'ssl') {
                    config(['mail.mailers.smtp.scheme' => 'smtps']);
                } elseif ($encryption === 'tls') {
                    config(['mail.mailers.smtp.scheme' => null]);
                } else {
                    config(['mail.mailers.smtp.scheme' => null]);
                }
                
                config(['mail.from.address' => Settings::system('mail_from_address', config('mail.from.address'))]);
                config(['mail.from.name' => Settings::system('mail_from_name', config('mail.from.name'))]);
            }

            // Forget resolved mailer instances to apply updated configuration (skip during testing)
            if (!app()->runningUnitTests() && app()->resolved('mail.manager')) {
                app()->make('mail.manager')->forgetMailers();
            }

            \Illuminate\Support\Facades\Mail::to($recipient)->send(new \App\Mail\SystemTestMail($recipient));

            return response()->json([
                'success' => true,
                'message' => "Test email successfully sent to {$recipient}."
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "SMTP Test Failed: " . $e->getMessage()
            ], 500);
        }
    }
}
