<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VoucherController extends Controller
{
    /**
     * Check if voucher code is valid
     */
    public function check(Request $request)
    {
        $code = $request->query('code');
        
        if (!$code) {
            return response()->json(['valid' => false, 'message' => 'No code provided']);
        }

        $voucher = Voucher::where('code', strtoupper($code))->first();

        if (!$voucher) {
            return response()->json(['valid' => false, 'message' => 'Voucher not found']);
        }

        // ✅ Handle both is_used and is_redeemed
        $isUsed = $voucher->is_used ?? $voucher->is_redeemed ?? false;

        if ($isUsed) {
            return response()->json(['valid' => false, 'message' => 'Voucher already used']);
        }

        if ($voucher->isExpired()) {
            return response()->json([
                'valid' => false, 
                'message' => 'Voucher expired on ' . $voucher->expires_at->format('M d, Y')
            ]);
        }

        $daysLeft = $voucher->daysRemaining();
        $expiryWarning = $daysLeft <= 7 ? " (Expires in {$daysLeft} days!)" : "";

        return response()->json([
            'valid' => true,
            'amount' => $voucher->amount,
            'expires_at' => $voucher->expires_at->format('M d, Y'),
            'days_remaining' => $daysLeft,
            'message' => '✅ Valid voucher worth ₱' . number_format($voucher->amount, 2) . $expiryWarning
        ]);
    }

    /**
     * Apply voucher to current order
     */
    public function apply(Request $request)
    {
        Log::info('=== VOUCHER APPLICATION STARTED (VoucherController) ===');
        Log::info('Request data:', $request->all());

        $request->validate([
            'code' => 'required|string',
            'source' => 'required|in:USB,QR,Bluetooth',
        ]);

        $code = strtoupper($request->input('code'));
        $source = $request->input('source');

        Log::info("Applying voucher: {$code}, Source: {$source}");

        // ✅ Find voucher - handle both is_used and is_redeemed columns
        $voucher = Voucher::where('code', $code)
            ->where(function($query) {
                // Handle both column names and null values
                $query->where('is_redeemed', false)
                      ->orWhereNull('is_redeemed')
                      ->orWhere('is_used', false)
                      ->orWhereNull('is_used');
            })
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$voucher) {
            Log::warning('Voucher not found or invalid');
            
            // Check if it exists at all
            $anyVoucher = Voucher::where('code', $code)->first();
            if ($anyVoucher) {
                $isUsed = $anyVoucher->is_used ?? $anyVoucher->is_redeemed ?? false;
                
                Log::info('Voucher exists but invalid:', [
                    'code' => $anyVoucher->code,
                    'is_redeemed' => $anyVoucher->is_redeemed ?? 'N/A',
                    'is_used' => $anyVoucher->is_used ?? 'N/A',
                    'expires_at' => $anyVoucher->expires_at
                ]);

                if ($isUsed) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Voucher already used'
                    ], 400);
                }

                if ($anyVoucher->expires_at <= Carbon::now()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Voucher expired on ' . $anyVoucher->expires_at->format('M d, Y')
                    ], 400);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found'
            ], 400);
        }

        Log::info('Valid voucher found:', [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'amount' => $voucher->amount
        ]);

        // ✅ Get the correct session key based on source
        $sessionKey = match($source) {
            'USB' => 'usb.order',
            'Bluetooth' => 'bluetooth.order',
            'QR' => 'order',
            default => 'order'
        };

        Log::info("Using session key: {$sessionKey}");

        $order = session($sessionKey);

        if (!$order) {
            Log::error("No order found in session key: {$sessionKey}");
            Log::info('Available sessions:', session()->all());
            
            return response()->json([
                'success' => false,
                'message' => 'No active order found'
            ], 400);
        }

        Log::info('Order found:', $order);

        // Check if voucher already applied
        if (!empty($order['voucher_applied'])) {
            return response()->json([
                'success' => false,
                'message' => 'A voucher has already been applied to this order'
            ], 400);
        }

        // Apply voucher discount
        // ✅ Handle both 'total' and 'calculated_total' keys
        $originalTotal = $order['calculated_total'] ?? $order['total'] ?? 0;
        $discount = min($voucher->amount, $originalTotal);
        $newTotal = max(0, $originalTotal - $discount);

        Log::info('Applying discount:', [
            'original_total' => $originalTotal,
            'voucher_amount' => $voucher->amount,
            'discount' => $discount,
            'new_total' => $newTotal
        ]);

        $order['voucher_applied'] = true;
        $order['voucher_code'] = $code;
        $order['voucher_id'] = $voucher->id;
        $order['voucher_discount'] = $discount;
        $order['original_total'] = $originalTotal;
        
        // ✅ Update both keys for compatibility
        $order['total'] = $newTotal;
        $order['calculated_total'] = $newTotal;

        session([$sessionKey => $order]);

        Log::info('Voucher applied, session updated');

        // ✅ DON'T redeem voucher yet - only redeem when payment is actually completed
        // $voucher->redeem();  // Remove this line
        
        Log::info('=== VOUCHER APPLICATION COMPLETED ===');

        return response()->json([
            'success' => true,
            'discount' => $discount,
            'new_total' => $newTotal,
            'message' => '✅ Voucher applied! You saved ₱' . number_format($discount, 2)
        ]);
    }
}