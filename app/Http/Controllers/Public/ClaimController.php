<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ClaimService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClaimController extends Controller
{
    protected $claimService;

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
    }

    /**
     * Show the claim form.
     *
     * @param string $code
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(string $code)
    {
        // Check if claim session is closed
        if (!config('app.claim_open', true)) {
            return redirect()->route('public.claim-closed');
        }

        try {
            $voucher = $this->claimService->validateVoucherForClaim($code);
            $pics = \App\Models\Pic::orderBy('name')->get();

            return view('public.claim', [
                'voucher' => $voucher,
                'code' => $code,
                'pics' => $pics,
            ]);
        } catch (ValidationException $e) {
            return view('public.claim-error', [
                'code' => $code,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the claim closed page.
     *
     * @return \Illuminate\View\View
     */
    public function closed()
    {
        return view('public.claim-closed');
    }

    /**
     * Process the claim.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Restore validation logic
            // The QueryException happened because validation was bypassed.
            // Since we disabled the ValidatePostSize middleware, validation should now run correctly.
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

            return redirect()->route('public.vouchers', ['token' => $claim->public_token]);
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Claim Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            
            // Temporary: Show full error details for debugging
            return back()
                ->withInput()
                ->with('error', 'Error Detail: ' . $e->getMessage());
        }
    }
}
