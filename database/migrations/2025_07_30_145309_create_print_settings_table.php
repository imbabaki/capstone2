<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('print_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('paper_size', ['A4', 'Short', 'Legal']);
            $table->enum('color', ['color', 'grayscale']);
            $table->decimal('price', 8, 2);
            $table->timestamps();
            
            $table->unique(['paper_size', 'color']); // Ensure one setting per combination
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_settings');
    }
};
