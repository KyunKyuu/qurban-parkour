<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\InitialVoucher;
use App\Services\ClaimService;
use App\Services\QurbanPricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClaimController extends Controller
{
    public function __construct(
        protected ClaimService $claimService,
        protected QurbanPricingService $pricingService
    ) {
    }

    public function show(string $code)
    {
        if ($this->isContributionClosed()) {
            return redirect()->route('public.claim-closed');
        }

        try {
            $voucher = $this->claimService->validateVoucherForClaim($code);

            return view('public.claim', $this->viewPayload($voucher, $code));
        } catch (ValidationException $e) {
            return view('public.claim-error', [
                'code' => $code,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function closed()
    {
        return view('public.claim-closed', [
            'closingLabel' => config('qurban.closing_label'),
        ]);
    }

    public function store(Request $request)
    {
        if ($this->isContributionClosed()) {
            return redirect()->route('public.claim-closed');
        }

        try {
            $validated = $request->validate($this->validationRules());
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
            ], $validated['code']);

            return redirect()->route('public.certificate', ['token' => $claim->public_token]);
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Claim Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Kontribusi belum dapat diproses. ' . $e->getMessage());
        }
    }

    public function downloadCertificate(string $token)
    {
        $claim = $this->claimService->getClaimByToken($token);

        abort_if(!$claim, 404, 'Sertifikat tidak ditemukan.');

        return $this->claimService->downloadCertificate($claim);
    }

    protected function validationRules(): array
    {
        $categoryKeys = implode(',', array_keys($this->pricingService->categories()));

        return [
            'code'   => 'required|string',
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

    protected function viewPayload(InitialVoucher $voucher, string $code): array
    {
        $categories = $this->pricingService->options();

        return [
            'voucher'          => $voucher,
            'code'             => $code,
            'categoryOptions'  => $categories,
            'picLabel'         => $voucher->pic?->name ?? config('qurban.default_pic_label'),
            'communityLabel'   => $voucher->community?->name,
            'bankAccountLabel' => config('qurban.bank_account_label'),
            'campaignName'     => config('qurban.campaign_name'),
            'campaignSubtitle' => config('qurban.campaign_subtitle'),
            'closingLabel'     => config('qurban.closing_label'),
        ];
    }

    protected function isContributionClosed(): bool
    {
        if (!config('app.claim_open', true) || !config('qurban.claim_open', true)) {
            return true;
        }

        $closingAt = config('qurban.closing_at');

        return $closingAt
            ? now()->greaterThan(Carbon::parse($closingAt))
            : false;
    }
}
