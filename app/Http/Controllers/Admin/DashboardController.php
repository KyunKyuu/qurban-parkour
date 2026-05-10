<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Pic;
use App\Services\QurbanPricingService;
use App\Services\QurbanSettingsService;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __construct(
        protected QurbanPricingService $pricingService,
        protected QurbanSettingsService $settingsService
    ) {
    }

    public function index()
    {
        $amountExpression = $this->amountExpression();
        $kpis = [
            'total_claims' => Claim::count(),
            'total_participants' => Claim::distinct('email')->count('email'),
            'total_amount' => (float) Claim::selectRaw("COALESCE(SUM({$amountExpression}), 0) AS total_amount")->value('total_amount'),
            'total_certificates' => Claim::whereNotNull('certificate_generated_at')->count(),
            'total_pics' => Pic::count(),
            'active_pics' => Pic::where('is_active', true)->count(),
        ];

        $recentClaims = Claim::with(['initialVoucher.pic', 'pic'])
            ->latest()
            ->take(6)
            ->get();

        $recentCertificates = Claim::with(['initialVoucher.pic', 'pic'])
            ->whereNotNull('certificate_generated_at')
            ->latest('certificate_generated_at')
            ->take(6)
            ->get();

        $categoryStats = Claim::selectRaw(
            "COALESCE(category_label, 'Legacy Contribution') AS label, COUNT(*) AS total_claims, COALESCE(SUM({$amountExpression}), 0) AS total_amount"
        )
            ->groupBy('label')
            ->orderByDesc('total_amount')
            ->get();

        $endDate = now();
        $startDate = now()->subDays(29);

        $dailyStats = Claim::selectRaw(
            "DATE(created_at) AS date, COALESCE(SUM({$amountExpression}), 0) AS total_amount, COUNT(*) AS total_claims"
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartLabels = [];
        $chartData = [];
        $chartClaims = [];

        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $chartLabels[] = $startDate->copy()->addDays($i)->format('d M');

            $dayStat = $dailyStats[$date] ?? null;
            $chartData[] = $dayStat ? (float) $dayStat->total_amount : 0;
            $chartClaims[] = $dayStat ? (int) $dayStat->total_claims : 0;
        }

        $paymentStats = Claim::selectRaw(
            "payment_method, COUNT(*) AS total_claims, COALESCE(SUM({$amountExpression}), 0) AS total_amount"
        )
            ->groupBy('payment_method')
            ->get();

        $topPics = Claim::with(['initialVoucher.pic', 'pic'])
            ->get()
            ->groupBy(function (Claim $claim) {
                return $claim->pic?->name ?? $claim->initialVoucher?->pic?->name ?? 'Tanpa PIC';
            })
            ->map(function (Collection $claims, string $picName) {
                return [
                    'name' => $picName,
                    'total_claims' => $claims->count(),
                    'total_amount' => $claims->sum(fn (Claim $claim) => $claim->total_donation_amount),
                ];
            })
            ->sortByDesc('total_amount')
            ->take(5)
            ->values();

        $pricingOptions = $this->pricingService->options();
        $settings = $this->settingsService->current();

        $patunganPool = [
            'total_collected' => (float) Claim::where('category_type', 'PATUNGAN')->sum('contribution_amount'),
            'claim_count' => Claim::where('category_type', 'PATUNGAN')->count(),
        ];

        $patunganTargetLabels = collect($this->pricingService->patunganTargets())
            ->map(fn (string $key) => $settings['categories'][$key]['label'] ?? $key)
            ->values();

        return view('admin.dashboard', compact(
            'kpis',
            'recentClaims',
            'recentCertificates',
            'categoryStats',
            'paymentStats',
            'chartLabels',
            'chartData',
            'chartClaims',
            'topPics',
            'patunganPool',
            'pricingOptions',
            'settings',
            'patunganTargetLabels'
        ));
    }

    protected function amountExpression(): string
    {
        return 'COALESCE(contribution_amount, COALESCE(zakat_fitrah_amount, 0) + COALESCE(zakat_mal_amount, 0) + COALESCE(infaq_amount, 0) + COALESCE(sodaqoh_amount, 0))';
    }
}
