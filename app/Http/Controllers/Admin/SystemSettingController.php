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
}
