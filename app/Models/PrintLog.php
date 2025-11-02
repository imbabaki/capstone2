<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrintLog extends Model
{
    protected $fillable = [
        'source',
        'file_name',
        'copies',
        'page_count',
        'paper_size',
        'color_option',
        'rate_per_page',
        'total_amount',
    ];
}