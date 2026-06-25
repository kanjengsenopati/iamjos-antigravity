<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function index()
    {
        $branding = $this->getBrandingData();
        return view('admins.auth.forgot-password', compact('branding'));
    }

    public function post(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'The email address is not registered in our system.',
        ]);

        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            // Get custom characters configuration from system settings
            $allowedChars = \App\Facades\Settings::system('password_reset_characters', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%');
            
            // Generate a random 12-character password
            $newPassword = '';
            $maxIndex = strlen($allowedChars) - 1;
            for ($i = 0; $i < 12; $i++) {
                $newPassword .= $allowedChars[random_int(0, $maxIndex)];
            }
            
            // Hash and update the user's password in the global database (shared across all journals)
            $user->update([
                'password' => Hash::make($newPassword),
            ]);

            // Determine active journal context for custom Mail From Name and Email
            $journal = current_journal();
            $fromName = $journal ? $journal->name : \App\Facades\Settings::system('mail_from_name', config('mail.from.name', 'IAMJOS System'));
            $fromEmail = \App\Facades\Settings::system('mail_from_address', config('mail.from.address', 'noreply@example.com'));

            try {
                // Dynamically override mailer settings from system settings database
                $mailer = \App\Facades\Settings::system('mail_mailer');
                if ($mailer) {
                    config(['mail.default' => $mailer]);
                    config(['mail.mailers.smtp.host' => \App\Facades\Settings::system('mail_host', config('mail.mailers.smtp.host'))]);
                    config(['mail.mailers.smtp.port' => (int) \App\Facades\Settings::system('mail_port', config('mail.mailers.smtp.port'))]);
                    config(['mail.mailers.smtp.username' => \App\Facades\Settings::system('mail_username', config('mail.mailers.smtp.username'))]);
                    config(['mail.mailers.smtp.password' => \App\Facades\Settings::system('mail_password', config('mail.mailers.smtp.password'))]);
                    
                    $encryption = \App\Facades\Settings::system('mail_encryption');
                    if ($encryption === 'ssl') {
                        config(['mail.mailers.smtp.scheme' => 'smtps']);
                    } else {
                        config(['mail.mailers.smtp.scheme' => null]);
                    }
                }

                // Invalidate cache for mailer instance to apply new configurations
                if (!app()->runningUnitTests() && app()->resolved('mail.manager')) {
                    app()->make('mail.manager')->forgetMailers();
                }

                Mail::send('emails.forgot_password', [
                    'admin' => $user,
                    'new_password' => $newPassword
                ], function ($message) use ($user, $fromEmail, $fromName, $journal) {
                    $message->to($user->email);
                    $message->from($fromEmail, $fromName);
                    $message->subject('Reset Password - ' . ($journal ? $journal->name : config('app.name', 'IAMJOS')));
                });
            } catch (\Exception $e) {
                Log::error('Gagal mengirim email reset password acak: ' . $e->getMessage());
                return back()->withErrors(['email' => 'Failed to send recovery email. Please try again.']);
            }
        }

        $branding = $this->getBrandingData();
        return view('admins.auth.success', compact('branding'));
    }

    public function changePassword()
    {
        $token = request()->token;
        if (!$token) {
            return redirect(route('forgot-password'))->with('error', 'Invalid or expired password reset token.');
        }

        $tokenCheck = DB::table('password_reset_tokens')->where('token', $token)->first();

        if (!$tokenCheck) {
            return redirect(route('forgot-password'))->with('error', 'Invalid or expired password reset token.');
        }

        // Set type 'ADMIN' to preserve compatibility with standard view hidden inputs
        $type = 'ADMIN';
        $branding = $this->getBrandingData();
        return view('admins.auth.change-password', compact('type', 'branding'));
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $reset = DB::table('password_reset_tokens')->where('token', $request->token)->first();
        
        if (!$reset) {
            return back()->withErrors(['token' => 'Invalid or expired password reset link.']);
        }

        $user = User::where('email', $reset->email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        return back()->with('success', 'Password has been successfully reset. Please login with your new password.');
    }

    /**
     * Get default portal branding data.
     */
    protected function getBrandingData(): array
    {
        return [
            'name' => config('app.name', 'IAMJOS'),
            'acronym' => 'IAMJOS',
            'description' => 'Indonesian Academic Journal System',
            'logo_url' => null,
            'cover_url' => null,
            'headline' => 'Advance Your Academic Research',
            'tagline' => 'A modern platform for managing academic journal submissions, peer reviews, and publications with streamlined workflows.',
        ];
    }
}
