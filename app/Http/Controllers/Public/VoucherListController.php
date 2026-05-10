<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ClaimService;
use App\Services\QurbanPricingService;

class VoucherListController extends Controller
{
    public function __construct(
        protected ClaimService $claimService,
        protected QurbanPricingService $pricingService
    ) {
    }

    /**
     * Show the participant summary and certificate access.
     *
     * @param string $token
     * @return \Illuminate\View\View
     */
    public function show(string $token)
    {
        $claim = $this->claimService->getClaimByToken($token);

        if (!$claim) {
            abort(404, 'Data kontribusi tidak ditemukan.');
        }

        $settingsAudit = $this->pricingService->auditClaim($claim);
        $sourceLabel = $claim->initial_voucher_id ? 'Voucher PIC' : 'Direct web';

        return view('public.vouchers', [
            'claim' => $claim,
            'downloadRoute' => route('public.certificate.download', $claim->public_token),
            'settingsAudit' => $settingsAudit,
            'sourceLabel' => $sourceLabel,
        ]);
    }
}
