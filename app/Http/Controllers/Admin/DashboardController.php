<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Voucher;
use App\Models\PrintSetting;
use App\Models\PrintLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get overview statistics
        $totalSales = PrintLog::sum('total_amount') ?? 0;
        $totalPrints = PrintLog::count();
        $totalVouchers = Voucher::count();
        $activeVouchers = Voucher::valid()->count();
        $priceSettings = PrintSetting::count();

        // Get emergency shutdown status
        $emergencyShutdown = SystemSetting::isEmergencyShutdown();

        return view('admin.dashboard', compact(
            'totalSales',
            'totalPrints',
            'totalVouchers',
            'activeVouchers',
            'priceSettings',
            'emergencyShutdown'
        ));
    }

    public function toggleEmergencyShutdown(Request $request)
    {
        $currentStatus = SystemSetting::get('emergency_shutdown', '0');
        $newStatus = $currentStatus === '1' ? '0' : '1';

        SystemSetting::set('emergency_shutdown', $newStatus);

        $message = $newStatus === '1'
            ? 'Emergency shutdown activated! All customer routes are now disabled.'
            : 'Emergency shutdown deactivated! Machine is now operational.';

        return redirect()->route('admin.dashboard')->with('success', $message);
    }

    public function checkEmergencyStatus()
    {
        return response()->json([
            'emergency_shutdown' => SystemSetting::isEmergencyShutdown(),
            'status' => SystemSetting::isEmergencyShutdown() ? 'disabled' : 'operational'
        ]);
    }

    public function clearDatabase(Request $request)
    {
        try {
            $driver = \DB::getDriverName();

            // Disable foreign key checks based on database driver
            if ($driver === 'mysql') {
                \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            } elseif ($driver === 'sqlite') {
                \DB::statement('PRAGMA foreign_keys = OFF;');
            }

            // Clear all main tables
            PrintLog::truncate();
            Voucher::truncate();
            Sale::truncate();

            // Reset system settings except admin credentials and emergency shutdown
            SystemSetting::whereNotIn('key', ['admin_username', 'admin_password', 'emergency_shutdown'])->delete();

            // Re-enable foreign key checks
            if ($driver === 'mysql') {
                \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif ($driver === 'sqlite') {
                \DB::statement('PRAGMA foreign_keys = ON;');
            }

            \Log::info('Database cleared by admin', [
                'admin' => session('admin_username'),
                'timestamp' => now(),
                'driver' => $driver
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Database cleared successfully! All sales, print logs, and vouchers have been reset to zero.');
        } catch (\Exception $e) {
            \Log::error('Database clear failed', [
                'error' => $e->getMessage(),
                'admin' => session('admin_username')
            ]);

            return redirect()->route('admin.dashboard')->with('error', 'Failed to clear database: ' . $e->getMessage());
        }
    }
}
