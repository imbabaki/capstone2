<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;  // ← ADD THIS LINE
use Carbon\Carbon;

class Voucher extends Model
{
    protected $fillable = [
        'code',
        'amount',
        'is_used',
        'is_redeemed',
        'used_at',
        'expires_at',
        'expiration_days',
        'source',
    ];

    protected $casts = [
        'is_used' => 'boolean',
        'is_redeemed' => 'boolean',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Generate a unique voucher code
     */
    public static function generateCode(): string
    {
        do {
            $code = 'PRINT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Check if voucher is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return Carbon::now()->isAfter($this->expires_at);
    }

    /**
     * Check if voucher is valid (not used and not expired)
     */
    public function isValid(): bool
    {
        // Support both column names
        $isUsed = $this->is_used || $this->is_redeemed;
        return !$isUsed && !$this->isExpired();
    }

    /**
     * Get days remaining until expiration
     */
    public function daysRemaining(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        $days = Carbon::now()->diffInDays($this->expires_at, false);
        return $days > 0 ? (int) $days : 0;
    }

    /**
     * Redeem this voucher
     */
    public function redeem(): bool
    {
        // Support both column names
        $isUsed = $this->is_used || $this->is_redeemed;
        
        if ($isUsed || $this->isExpired()) {
            return false;
        }

        // Set both columns for compatibility
        if (Schema::hasColumn('vouchers', 'is_used')) {
            $this->is_used = true;
        }
        if (Schema::hasColumn('vouchers', 'is_redeemed')) {
            $this->is_redeemed = true;
        }
        
        $this->used_at = now();
        return $this->save();
    }

    /**
     * Scope for valid vouchers only
     */
    public function scopeValid($query)
    {
        return $query->where(function($q) {
                    // Support both column names
                    if (Schema::hasColumn('vouchers', 'is_used')) {
                        $q->where('is_used', false);
                    }
                    if (Schema::hasColumn('vouchers', 'is_redeemed')) {
                        $q->where('is_redeemed', false);
                    }
                })
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                });
    }
}