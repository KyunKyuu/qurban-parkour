<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Claim;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FundVerificationController extends Controller
{
    /**
     * Display a listing of financial days.
     */
    public function index()
    {
        // Group by "Financial Day" (Cutoff 20:00)
        // If created_at < 20:00, it belongs to that day.
        // If created_at >= 20:00, it belongs to the NEXT day.
        // Formula: DATE(SUBDATE(created_at, INTERVAL 20 HOUR)) ? No, wait.
        // Example: 15 Feb 19:00 -> 15 Feb.
        // Example: 15 Feb 21:00 -> 16 Feb.
        // So we add 4 hours?
        // 15 Feb 19:00 + 4h = 15 Feb 23:00 -> Date: 15 Feb.
        // 15 Feb 21:00 + 4h = 16 Feb 01:00 -> Date: 16 Feb.
        // Yes, adding 4 hours shifts the 20:00 boundary to 00:00 of the next day.

        $days = Claim::select(
                DB::raw('DATE(DATE_ADD(created_at, INTERVAL 4 HOUR)) as financial_date'),
                DB::raw('COUNT(*) as total_vouchers'),
                DB::raw('COALESCE(SUM(COALESCE(contribution_amount, COALESCE(zakat_fitrah_amount, 0) + COALESCE(zakat_mal_amount, 0) + COALESCE(infaq_amount, 0) + COALESCE(sodaqoh_amount, 0))), 0) as total_amount'),
                DB::raw('SUM(CASE WHEN verification_status = "VERIFIED" THEN 1 ELSE 0 END) as verified_count'),
                DB::raw('SUM(CASE WHEN verification_status = "ANOMALY" THEN 1 ELSE 0 END) as anomaly_count')
            )
            ->groupBy('financial_date')
            ->orderBy('financial_date', 'desc')
            ->paginate(10);

        return view('admin.fund-verification.index', compact('days'));
    }

    /**
     * Display details for a specific financial day.
     */
    public function show($date)
    {
        // Calculate range for this financial day
        // Day X starts from Day X-1 20:00:00 to Day X 20:00:00
        $endDate = Carbon::parse($date)->setTime(20, 0, 0);
        $startDate = $endDate->copy()->subDay();

        $claims = Claim::with(['initialVoucher.batch', 'initialVoucher.pic'])
            ->where('created_at', '>', $startDate)
            ->where('created_at', '<=', $endDate)
            ->latest()
            ->get();

        $stats = [
            'total_vouchers' => $claims->count(),
            'total_amount' => $claims->sum('total_donation_amount'),
            'verified' => $claims->where('verification_status', 'VERIFIED')->count(),
            'pending' => $claims->where('verification_status', 'PENDING')->count(),
            'anomaly' => $claims->where('verification_status', 'ANOMALY')->count(),
        ];

        return view('admin.fund-verification.show', compact('claims', 'date', 'stats'));
    }

    /**
     * Verify all pending claims for a specific day.
     */
    public function verifyDay(Request $request, $date)
    {
        $endDate = Carbon::parse($date)->setTime(20, 0, 0);
        $startDate = $endDate->copy()->subDay();

        Claim::where('created_at', '>', $startDate)
            ->where('created_at', '<=', $endDate)
            ->where('verification_status', 'PENDING')
            ->update([
                'verification_status' => 'VERIFIED',
                'verified_at' => now(),
            ]);

        return redirect()->route('admin.fund-verification.show', $date)
            ->with('success', 'Semua data pending pada tanggal ' . $date . ' telah diverifikasi.');
    }

    /**
     * Mark a specific claim as anomaly.
     */
    public function markAnomaly(Request $request, Claim $claim)
    {
        $request->validate([
            'note' => 'required|string|max:255',
        ]);

        $claim->update([
            'verification_status' => 'ANOMALY',
            'verification_note' => $request->note,
            'verified_at' => now(), // Anomaly is also a processed state
        ]);

        return back()->with('success', 'Voucher ditandai sebagai anomali.');
    }
}
