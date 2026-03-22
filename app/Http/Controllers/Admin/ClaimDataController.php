<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\InitialVoucher;
use App\Models\Pic;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClaimDataController extends Controller
{
    protected $claimService;

    public function __construct(\App\Services\ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

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
     * Store a new claim.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string',
                'pic_id' => 'required|exists:pics,id',
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:100',
                'phone' => 'required|string|max:30',
                'zakat_fitrah_amount' => 'nullable|numeric|min:0',
                'zakat_mal_amount' => 'nullable|numeric|min:0',
                'infaq_amount' => 'nullable|numeric|min:0',
                'sodaqoh_amount' => 'nullable|numeric|min:0',
                'payment_method' => 'required|in:cash,transfer',
                'transfer_destination' => 'nullable|required_if:payment_method,transfer|string|max:255',
                'transfer_proof' => 'nullable|required_if:payment_method,transfer|file|mimes:jpg,jpeg,png,pdf|max:4096',
            ]);

            // Check if voucher exists first
            $voucher = InitialVoucher::where('code', $validated['code'])->first();
            if (!$voucher) {
                return back()
                    ->withInput()
                    ->withErrors(['code' => "Kode voucher '{$validated['code']}' tidak ditemukan. Silakan cek kembali kode voucher."]);
            }

            // Check voucher status
            if ($voucher->status !== 'ASSIGNED') {
                $message = match ($voucher->status) {
                    'UNASSIGNED' => "Voucher '{$validated['code']}' belum di-assign ke PIC manapun. Status voucher: UNASSIGNED.",
                    'CLAIMED' => "Voucher '{$validated['code']}' sudah pernah diklaim pada " . ($voucher->claimed_at ? $voucher->claimed_at->format('d M Y H:i') : '-') . ". Status voucher: CLAIMED.",
                    'VOID' => "Voucher '{$validated['code']}' sudah tidak berlaku (VOID).",
                    default => "Voucher '{$validated['code']}' tidak dapat diklaim. Status: {$voucher->status}.",
                };

                return back()
                    ->withInput()
                    ->withErrors(['code' => $message]);
            }

            // Check if PIC matches
            if ($voucher->assigned_pic_id != $validated['pic_id']) {
                $pic = Pic::find($validated['pic_id']);
                $assignedPic = $voucher->pic;

                $message = "Voucher '{$validated['code']}' tidak sesuai dengan PIC yang dipilih.\n";
                $message .= "PIC yang dipilih: " . ($pic ? $pic->name : 'Unknown') . "\n";
                $message .= "PIC yang seharusnya: " . ($assignedPic ? $assignedPic->name : 'Unknown') . " (" . ($assignedPic ? $assignedPic->code : '') . ")\n";
                $message .= "\nSilakan pilih PIC yang sesuai dengan voucher ini.";

                return back()
                    ->withInput()
                    ->withErrors(['pic_id' => $message]);
            }

            $zakatFitrahAmount = isset($validated['zakat_fitrah_amount'])
                ? (float) $validated['zakat_fitrah_amount']
                : 0;
            $zakatMalAmount = isset($validated['zakat_mal_amount'])
                ? (float) $validated['zakat_mal_amount']
                : 0;
            $infaqAmount = isset($validated['infaq_amount'])
                ? (float) $validated['infaq_amount']
                : 0;
            $sodaqohAmount = isset($validated['sodaqoh_amount'])
                ? (float) $validated['sodaqoh_amount']
                : 0;
            $transferProofPath = null;

            if (
                ($validated['payment_method'] ?? 'cash') === 'transfer' &&
                $request->hasFile('transfer_proof')
            ) {
                $transferProofPath = $request->file('transfer_proof')->store('transfer-proofs', 'public');
            }

            $claim = $this->claimService->processClaim(
                $validated['code'],
                $validated['pic_id'],
                $validated['name'],
                $validated['email'],
                $validated['phone'],
                $zakatFitrahAmount,
                $zakatMalAmount,
                $infaqAmount,
                $sodaqohAmount,
                $validated['payment_method'],
                $validated['transfer_destination'] ?? null,
                $transferProofPath
            );

            return redirect()->route('admin.claims.index')
                ->with('success', 'Claim berhasil dibuat untuk ' . $claim->name);
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Admin Claim Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
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
