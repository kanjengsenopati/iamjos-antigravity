<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        // This method can be used to display the admin dashboard
        $isPasswordSafe = $this->isPasswordSafe(Auth::user()->password);
        return view('admins.dashboard.index', compact('isPasswordSafe'));
    }

    public function isPasswordSafe($password)
    {
        $datasets = ['12345678', '12345', '123', 'bismillah', 'admin123', 'admin', 'qwerty', 'password', 'welcome', '123abc', '123qwe', 'iloveyou', 'abc123', '123456789', '1234567', '1234', '123456', 'master', '696969', 'mustang', 'batman', 'anjing', 'sayang', 'cinta', 'kucing', 'indonesia', 'ganteng', 'cantik', '1234567890', 'qazwsx', '987654321', '1q2w3e4r', '123123', '555555'];

        foreach ($datasets as $dataset) {
            if (Hash::check($dataset, $password)) {
                return false;
            }
        }
        return true;
    }
}
