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
    public function index(Request $request)
    {
        // Store intended journal for post-login redirect
        if ($request->has('intended_journal')) {
            session(['intended_journal' => $request->query('intended_journal')]);
        }

        return view('admins.auth.login');
    }

    /**
     * Menangani proses autentikasi.
     */
    public function authenticate(AuthRequest $request)
    {
        $credentials = $request->validated();

        // Determine if input is email or username
        $loginInput = $credentials['email'];
        $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Find user by either email or username
        $user = User::where($fieldType, $loginInput)->first();

        // 2. Guard Clause: Jika user tidak ada, langsung kembalikan error.
        // Pesan dibuat umum untuk keamanan.
        if (!$user) {
            return back()->with('warning', 'Email/Username atau password tidak sesuai.')
                ->withInput($request->only('email'));
        }

        // 3. Guard Clause: Jika akun user sedang terblokir.
        // if ($user->isBlocked()) {
        //     $blockMessage = "Akun Anda ditangguhkan hingga " . $user->blocked_until->format('d-m-Y H:i') . ". Silakan hubungi administrator.";
        //     return back()->with('warning', $blockMessage)
        //         ->withInput($request->only('email'));
        // }

        // 4. Coba lakukan autentikasi dengan kredensial & status aktif
        // Prepare credentials for Auth generic attempt
        $authCredentials = [
            $fieldType => $loginInput,
            'password' => $credentials['password']
        ];

        // Menggunakan guard 'admin' adalah praktik yang baik untuk memisahkan sesi user biasa dan admin
        if (Auth::guard('web')->attempt($authCredentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            /** @var \App\Models\User $user */
            $user = Auth::guard('web')->user();

            // Super Admin always goes to site admin
            if ($user->hasRole('Super Admin')) {
                session()->forget('intended_journal');
                return redirect()->route('admin.site.index');
            }

            // Check if there's an intended journal from session
            $intendedJournal = session('intended_journal');
            if ($intendedJournal) {
                session()->forget('intended_journal');
                return redirect()->route('journal.dashboard', $intendedJournal);
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
        return back()->with('warning', 'Email/Username atau password tidak sesuai.')
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
