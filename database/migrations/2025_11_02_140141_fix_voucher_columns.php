<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Rename is_used to is_redeemed if it doesn't exist
            if (Schema::hasColumn('vouchers', 'is_used') && !Schema::hasColumn('vouchers', 'is_redeemed')) {
                $table->renameColumn('is_used', 'is_redeemed');
            }
            // Or just add is_redeemed if both don't exist
            if (!Schema::hasColumn('vouchers', 'is_redeemed') && !Schema::hasColumn('vouchers', 'is_used')) {
                $table->boolean('is_redeemed')->default(false)->after('amount');
            }
        });
    }

    public function down()
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'is_redeemed')) {
                $table->renameColumn('is_redeemed', 'is_used');
            }
        });
    }
};