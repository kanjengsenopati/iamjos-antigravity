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
            $token = Str::random(60);
            
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => $token,
                    'created_at' => now()
                ]
            );

            // Pass token dynamically to user object for email template compatibility ($admin->token)
            $user->token = $token;

            try {
                Mail::send('emails.forgot_password', [
                    'admin' => $user
                ], function ($message) use ($user) {
                    $message->to($user->email);
                    $message->subject('Reset Password - ' . config('app.name', 'IAMJOS'));
                });
            } catch (\Exception $e) {
                Log::error('Gagal mengirim email lupa password: ' . $e->getMessage());
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
