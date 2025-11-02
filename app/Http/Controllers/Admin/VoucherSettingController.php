<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VoucherSetting;
use Illuminate\Http\Request;

class VoucherSettingController extends Controller
{
    public function index()
    {
        $expirationDays = VoucherSetting::getExpirationDays();
        
        return view('admin.voucher-settings', compact('expirationDays'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'expiration_days' => 'required|integer|min:1|max:365',
        ]);

        VoucherSetting::set('expiration_days', $request->input('expiration_days'));

        return back()->with('success', 'Voucher expiration setting updated successfully!');
    }
}