<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('print_logs', function (Blueprint $table) {
            $table->id();
            $table->string('source'); // 'USB', 'QR', 'Bluetooth'
            $table->string('file_name');
            $table->integer('copies')->default(1);
            $table->integer('page_count')->default(1);
            $table->string('paper_size'); // 'A4', 'Letter', 'Legal'
            $table->string('color_option'); // 'color', 'grayscale'
            $table->decimal('rate_per_page', 8, 2);
            $table->decimal('total_amount', 8, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('print_logs');
    }
};