<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrintLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        // Total prints by paper size
        $A4_total_print = PrintLog::where('paper_size', 'A4')->sum('page_count');
        $Short_total_print = PrintLog::where('paper_size', 'Letter')->sum('page_count');
        $Legal_total_print = PrintLog::where('paper_size', 'Legal')->sum('page_count');

        // Total prints by color
        $color_total_print = PrintLog::where('color_option', 'color')->sum('page_count');
        $grayscale_total_print = PrintLog::where('color_option', 'grayscale')->sum('page_count');

        // Total prints by source
        $USB_total_print = PrintLog::where('source', 'USB')->count();
        $Bluetooth_total_print = PrintLog::where('source', 'Bluetooth')->count();
        $QR_total_print = PrintLog::where('source', 'QR')->count();

        // Total pages and amount
        $total_pages_print = PrintLog::sum('page_count');
        $total_amount = PrintLog::sum('total_amount');

        return view('admin.sales-report', compact(
            'A4_total_print',
            'Short_total_print',
            'Legal_total_print',
            'color_total_print',
            'grayscale_total_print',
            'USB_total_print',
            'Bluetooth_total_print',
            'QR_total_print',
            'total_pages_print',
            'total_amount'
        ));
    }
}