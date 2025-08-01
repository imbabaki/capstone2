<?php

// app/Models/PrintSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintSetting extends Model
{
    use HasFactory;

    protected $fillable = ['paper_size', 'color_option', 'price'];
}
