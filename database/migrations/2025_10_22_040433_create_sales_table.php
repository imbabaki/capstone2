<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // Device identifier (for multi-kiosk setup)
            $table->string('device_id')->nullable();

            // Paper types — separate totals
            $table->integer('A4_total_print')->default(0);
            $table->integer('Short_total_print')->default(0);
            $table->integer('Legal_total_print')->default(0);

            // Print type totals
            $table->integer('color_total_print')->default(0);
            $table->integer('grayscale_total_print')->default(0);

            // Source-based totals
            $table->integer('USB_total_print')->default(0);
            $table->integer('Bluetooth_total_print')->default(0);
            $table->integer('QR_total_print')->default(0);

            // Total pages printed (overall)
            $table->integer('total_pages_print')->default(0);

            // Financials
            $table->decimal('total_amount', 10, 2)->default(0.00);

            // Date/time tracking
            $table->timestamps(); // includes created_at and updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
