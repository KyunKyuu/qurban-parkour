<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Pic;
use App\Services\CertificateService;
use App\Services\ClaimService;
use App\Services\QurbanPricingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClaimDataController extends Controller
{
    public function __construct(
        protected ClaimService $claimService,
        protected CertificateService $certificateService,
        protected QurbanPricingService $pricingService
    ) {
    }

    public function index(Request $request)
    {
        $query = $this->applyListFilters(
            Claim::with(['initialVoucher.pic', 'pic']),
            $request
        );
        $amountExpression = $this->amountExpression();
        $hasActiveFilters = $this->hasActiveFilters($request);
        $statsScopeLabel = $hasActiveFilters ? 'Sesuai filter aktif' : 'Semua data';

        $stats = $this->summarizeQuery(clone $query, $amountExpression);
        $claims = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $pics = Pic::orderBy('name')->get();
        $categories = $this->pricingService->options();

        return view('admin.claims.index', compact('claims', 'pics', 'stats', 'categories', 'statsScopeLabel'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate($this->rules());
            $transferProofPath = null;

            if (
                ($validated['payment_method'] ?? 'cash') === 'transfer' &&
                $request->hasFile('transfer_proof')
            ) {
                $transferProofPath = $request->file('transfer_proof')->store('transfer-proofs', 'public');
            }

            $claim = $this->claimService->processClaim([
                ...$validated,
                'transfer_proof_path' => $transferProofPath,
            ], $validated['code'] ?? null);

            return redirect()->route('admin.claims.index')
                ->with('success', 'Kontribusi berhasil dicatat untuk ' . $claim->name);
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
                ->with('error', 'Kontribusi belum dapat disimpan. ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $claim = Claim::with([
            'initialVoucher.pic',
            'pic',
        ])->findOrFail($id);

        return view('admin.claims.show', compact('claim'));
    }

    public function update(Request $request, $id)
    {
        $claim = Claim::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:30',
            'instagram_username' => 'nullable|string|max:100',
            'payment_method' => 'required|in:cash,transfer',
            'transfer_destination' => 'nullable|required_if:payment_method,transfer|string|max:255',
        ]);

        $claim->update($validated);

        return redirect()->route('admin.claims.show', $claim->id)->with('success', 'Data kontribusi diperbarui.');
    }

    public function destroy($id)
    {
        $claim = Claim::findOrFail($id);
        $claim->delete();

        return redirect()->route('admin.claims.index')->with('success', 'Data kontribusi berhasil dihapus.');
    }

    public function downloadCertificate($id)
    {
        $claim = Claim::findOrFail($id);

        return $this->certificateService->download($claim);
    }

    protected function rules(): array
    {
        $categoryKeys = implode(',', array_keys($this->pricingService->categories()));

        return [
            'code' => 'nullable|string',
            'pic_id' => 'nullable|exists:pics,id',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'required|string|max:30',
            'instagram_username' => 'nullable|string|max:100',
            'category_type' => 'required|in:' . $categoryKeys,
            'contribution_amount' => 'nullable|required_if:category_type,PATUNGAN|numeric|min:1000',
            'payment_method' => 'required|in:cash,transfer',
            'transfer_destination' => 'nullable|required_if:payment_method,transfer|string|max:255',
            'transfer_proof' => 'nullable|required_if:payment_method,transfer|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ];
    }

    protected function amountExpression(): string
    {
        return 'COALESCE(contribution_amount, COALESCE(zakat_fitrah_amount, 0) + COALESCE(zakat_mal_amount, 0) + COALESCE(infaq_amount, 0) + COALESCE(sodaqoh_amount, 0))';
    }

    protected function applyListFilters($query, Request $request)
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('pic_id')) {
            $query->where(function ($subQuery) use ($request) {
                $subQuery->where('pic_id', $request->pic_id)
                    ->orWhereHas('initialVoucher', function ($voucherQuery) use ($request) {
                        $voucherQuery->where('assigned_pic_id', $request->pic_id);
                    });
            });
        }

        if ($request->filled('category_type')) {
            $query->where('category_type', $request->category_type);
        }

        if ($request->certificate_status === 'generated') {
            $query->whereNotNull('certificate_generated_at');
        }

        if ($request->certificate_status === 'missing') {
            $query->whereNull('certificate_generated_at');
        }

        if ($request->source_channel === 'direct') {
            $query->whereNull('initial_voucher_id');
        }

        if ($request->source_channel === 'voucher') {
            $query->whereNotNull('initial_voucher_id');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('instagram_username', 'like', "%{$search}%")
                    ->orWhere('public_token', 'like', "%{$search}%")
                    ->orWhereHas('initialVoucher', function ($voucherQuery) use ($search) {
                        $voucherQuery->where('code', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    protected function summarizeQuery($query, string $amountExpression): array
    {
        return [
            'total_claims' => (clone $query)->count(),
            'total_amount' => (float) (clone $query)->selectRaw("COALESCE(SUM({$amountExpression}), 0) AS total_amount")->value('total_amount'),
            'certificates_generated' => (clone $query)->whereNotNull('certificate_generated_at')->count(),
        ];
    }

    protected function hasActiveFilters(Request $request): bool
    {
        foreach (['date_from', 'date_to', 'pic_id', 'category_type', 'certificate_status', 'source_channel', 'search'] as $key) {
            if ($request->filled($key)) {
                return true;
            }
        }

        return false;
    }
}
