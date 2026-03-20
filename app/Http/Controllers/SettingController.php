<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    public function index()
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $settings = Setting::orderBy('group')->orderBy('label')->get()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Check if user is admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $submittedSettings = $request->input('settings', []);

        // Get all boolean settings and default them to '0' if not submitted
        // (unchecked checkboxes are not sent by the browser)
        $allSettings = Setting::all();
        foreach ($allSettings as $setting) {
            if ($setting->type === 'boolean') {
                $value = isset($submittedSettings[$setting->key]) ? '1' : '0';
                Setting::set($setting->key, $value);
                // Remove from submitted array so it's not processed again below
                unset($submittedSettings[$setting->key]);
            }
        }

        // Handle remaining non-boolean settings
        foreach ($submittedSettings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                Setting::set($key, $value);
            }
        }

        Setting::clearCache();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil diperbarui');
    }
}
