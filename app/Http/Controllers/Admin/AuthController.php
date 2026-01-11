<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\AuthRequest;
use App\Models\Admin;
use App\Models\User;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login.
     */
    public function index()
    {
        return view('admins.auth.login');
    }

    /**
     * Menangani proses autentikasi.
     */
    public function authenticate(AuthRequest $request)
    {
        $credentials = $request->validated();

        // 1. Cari user berdasarkan email
        $user = User::where('email', $credentials['email'])->first();

        // 2. Guard Clause: Jika user tidak ada, langsung kembalikan error.
        // Pesan dibuat umum untuk keamanan.
        if (!$user) {
            return back()->with('warning', 'Email atau password tidak sesuai.')
                ->withInput($request->only('email'));
        }

        // 3. Guard Clause: Jika akun user sedang terblokir.
        // if ($user->isBlocked()) {
        //     $blockMessage = "Akun Anda ditangguhkan hingga " . $user->blocked_until->format('d-m-Y H:i') . ". Silakan hubungi administrator.";
        //     return back()->with('warning', $blockMessage)
        //         ->withInput($request->only('email'));
        // }

        // 4. Coba lakukan autentikasi dengan kredensial & status aktif
        // Menggunakan guard 'admin' adalah praktik yang baik untuk memisahkan sesi user biasa dan admin
        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::guard('web')->user();

            if ($user->hasRole('Super Admin')) {
                return redirect()->route('admin.site.index');
            }

            return redirect()->route('journal.select');
        }




        // 5. Jika autentikasi gagal (password salah atau akun tidak aktif)
        // $user->recordFailedLoginAttempt(); // Catat percobaan gagal

        // Cek lagi apakah percobaan ini menyebabkan akun terblokir
        // if ($user->isBlocked()) {
        //     $blockMessage = "Anda telah salah memasukkan password sebanyak " . Admin::MAX_LOGIN_ATTEMPTS . " kali. Akun Anda ditangguhkan selama 2 jam.";
        //     return back()->with('warning', $blockMessage)
        //         ->withInput($request->only('email'));
        // }

        // $remainingAttempts = Admin::MAX_LOGIN_ATTEMPTS - $user->login_attempts;
        // $warningMessage = $remainingAttempts > 0
        //     ? 'Password tidak sesuai. Kesempatan tersisa ' . $remainingAttempts . ' kali.'
        //     : 'Password tidak sesuai.';

        // return back()->with('warning', $warningMessage)
        //     ->withInput($request->only('email'));
        return back()->with('warning', 'Email atau password tidak sesuai.')
            ->withInput($request->only('email'));
    }

    /**
     * Menangani proses logout.
     */
    public function logout(Request $request)
    {
        // Sesuaikan dengan guard yang digunakan saat login
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function accessFromOffice($id)
    {
        // Find the admin by ID
        $admin = Admin::find($id);

        // Check if admin exists
        if (! $admin && $admin->is_operational == 0) {
            return redirect()->route('login')->with('error', 'Maaf, Anda tidak memiliki akses ke CMS');
        }

        // Log in the admin using their ID
        Auth::guard('web')->loginUsingId($id);

        // Redirect to the dashboard with a success message
        return redirect()->route('dashboard.index')->with('success', 'Berhasil Masuk ke Operational CMS');
    }
}
