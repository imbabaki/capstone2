<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class VoucherManagementController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');

        $query = Voucher::orderBy('created_at', 'desc');

        switch ($filter) {
            case 'active':
                $query->valid();
                break;
            case 'used':
                // Check both column names for compatibility
                $query->where(function($q) {
                    if (Schema::hasColumn('vouchers', 'is_redeemed')) {
                        $q->where('is_redeemed', true);
                    }
                    if (Schema::hasColumn('vouchers', 'is_used')) {
                        $q->orWhere('is_used', true);
                    }
                });
                break;
            case 'expired':
                // Check for unused/unredeemed vouchers that are expired
                $query->where(function($q) {
                    if (Schema::hasColumn('vouchers', 'is_redeemed')) {
                        $q->where('is_redeemed', false);
                    }
                    if (Schema::hasColumn('vouchers', 'is_used')) {
                        $q->where('is_used', false);
                    }
                })
                ->where('expires_at', '<', now());
                break;
        }

        $vouchers = $query->paginate(20);

        return view('admin.vouchers', compact('vouchers', 'filter'));
    }

    /**
     * Show manual voucher generation form
     */
    public function showGenerateForm()
    {
        $defaultExpirationDays = VoucherSetting::getExpirationDays();
        
        return view('admin.vouchers-generate', compact('defaultExpirationDays'));
    }

    /**
     * Generate voucher manually
     */
    public function generateManual(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:10000',
            'quantity' => 'required|integer|min:1|max:100',
            'expiration_days' => 'required|integer|min:1|max:365',
            'source' => 'nullable|string|max:50',
        ]);

        // ✅ Force cast to proper types
        $amount = (float) $request->input('amount');
        $quantity = (int) $request->input('quantity');
        $expirationDays = (int) $request->input('expiration_days');
        $source = $request->input('source', 'Manual');

        $generatedVouchers = [];

        for ($i = 0; $i < $quantity; $i++) {
            $voucher = Voucher::create([
                'code' => Voucher::generateCode(),
                'amount' => $amount,
                'source' => $source,
                'expires_at' => Carbon::now()->addDays($expirationDays), // ✅ Now properly typed as int
                'expiration_days' => $expirationDays,
            ]);

            $generatedVouchers[] = $voucher;
        }

        return view('admin.vouchers-generated', [
            'vouchers' => $generatedVouchers,
            'quantity' => $quantity,
            'amount' => $amount,
        ]);
    }
}