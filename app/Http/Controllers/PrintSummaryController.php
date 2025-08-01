namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrintSetting; // Assuming you saved prices in DB

class PrintSummaryController extends Controller
{
    public function show(Request $request)
    {
        $paper = $request->input('paper_size');
        $color = $request->input('color'); // 'color' or 'bw'
        $pages = (int) $request->input('pages');
        $copies = (int) $request->input('copies');

        // Fetch price per page from DB
        $setting = PrintSetting::where('paper_size', $paper)
                    ->where('color_type', $color)
                    ->first();

        $pricePerPage = $setting->price_per_page ?? 0;

        // Total cost
        $total = $pricePerPage * $pages * $copies;

        return view('print.summary', [
            'paper' => $paper,
            'color' => $color,
            'pages' => $pages,
            'copies' => $copies,
            'pricePerPage' => $pricePerPage,
            'total' => $total,
        ]);
    }
}
