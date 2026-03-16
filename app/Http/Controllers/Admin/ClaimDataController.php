<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use Illuminate\Http\Request;

class ClaimDataController extends Controller
{
    /**
     * Display claims data with filters.
     */
    public function index(Request $request)
    {
        $query = Claim::with(['initialVoucher.pic', 'initialVoucher.batch']);

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // PIC filter
        if ($request->filled('pic_id')) {
            $query->whereHas('initialVoucher', function ($q) use ($request) {
                $q->where('assigned_pic_id', $request->pic_id);
            });
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $claims = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get PICs for filter dropdown
        $pics = \App\Models\Pic::orderBy('name')->get();

        // Stats
        $stats = [
            'total_claims' => Claim::count(),
            'today_claims' => Claim::whereDate('created_at', today())->count(),
            'this_week_claims' => Claim::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        return view('admin.claims.index', compact('claims', 'pics', 'stats'));
    }

    /**
     * Show claim details.
     */
    public function show($id)
    {
        $claim = Claim::with([
            'initialVoucher.pic',
            'initialVoucher.batch',
            'merchantVouchers.merchant',
            'merchantVouchers.merchant.offer'
        ])->findOrFail($id);

        return view('admin.claims.show', compact('claim'));
    }

    /**
     * Update the claim.
     */
    public function update(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'zakat_fitrah_amount' => 'required|numeric|min:0',
            'zakat_mal_amount' => 'required|numeric|min:0',
            'infaq_amount' => 'required|numeric|min:0',
            'sodaqoh_amount' => 'required|numeric|min:0',
        ]);

        $claim->update($validated);

        return redirect()->route('admin.claims.index')->with('success', 'Claim updated successfully.');
    }

    /**
     * Soft delete the claim.
     */
    public function destroy($id)
    {
        $claim = Claim::findOrFail($id);
        $claim->delete();

        return redirect()->route('admin.claims.index')->with('success', 'Claim deleted successfully.');
    }
}
