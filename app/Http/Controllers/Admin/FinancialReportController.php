<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    public function index()
    {
        $expr = $this->amountExpr();

        // Overall KPIs
        $overall = Claim::selectRaw("
            COUNT(*) AS total_count,
            COALESCE(SUM({$expr}), 0) AS total_amount,
            SUM(CASE WHEN verification_status = 'VERIFIED' THEN 1 ELSE 0 END) AS verified_count,
            COALESCE(SUM(CASE WHEN verification_status = 'VERIFIED' THEN {$expr} ELSE 0 END), 0) AS verified_amount,
            SUM(CASE WHEN verification_status = 'PENDING' THEN 1 ELSE 0 END) AS pending_count,
            COALESCE(SUM(CASE WHEN verification_status = 'PENDING' THEN {$expr} ELSE 0 END), 0) AS pending_amount,
            SUM(CASE WHEN verification_status = 'ANOMALY' THEN 1 ELSE 0 END) AS anomaly_count,
            COALESCE(SUM(CASE WHEN verification_status = 'ANOMALY' THEN {$expr} ELSE 0 END), 0) AS anomaly_amount
        ")->first();

        // Per-category breakdown
        $categoryRows = Claim::selectRaw("
            COALESCE(category_label, category_type, 'Legacy') AS label,
            COUNT(*) AS total_count,
            COALESCE(SUM({$expr}), 0) AS total_amount,
            SUM(CASE WHEN verification_status = 'VERIFIED' THEN 1 ELSE 0 END) AS verified_count,
            COALESCE(SUM(CASE WHEN verification_status = 'VERIFIED' THEN {$expr} ELSE 0 END), 0) AS verified_amount,
            SUM(CASE WHEN verification_status = 'PENDING' THEN 1 ELSE 0 END) AS pending_count,
            COALESCE(SUM(CASE WHEN verification_status = 'PENDING' THEN {$expr} ELSE 0 END), 0) AS pending_amount,
            SUM(CASE WHEN verification_status = 'ANOMALY' THEN 1 ELSE 0 END) AS anomaly_count,
            COALESCE(SUM(CASE WHEN verification_status = 'ANOMALY' THEN {$expr} ELSE 0 END), 0) AS anomaly_amount
        ")
            ->groupBy(DB::raw("COALESCE(category_label, category_type, 'Legacy')"))
            ->orderByDesc('total_amount')
            ->get();

        // Payment method breakdown
        $paymentRows = Claim::selectRaw("
            COALESCE(payment_method, 'cash') AS method,
            COUNT(*) AS total_count,
            COALESCE(SUM({$expr}), 0) AS total_amount,
            SUM(CASE WHEN verification_status = 'VERIFIED' THEN 1 ELSE 0 END) AS verified_count,
            COALESCE(SUM(CASE WHEN verification_status = 'VERIFIED' THEN {$expr} ELSE 0 END), 0) AS verified_amount
        ")
            ->groupBy(DB::raw("COALESCE(payment_method, 'cash')"))
            ->orderByDesc('total_amount')
            ->get();

        return view('admin.financial-report.index', compact('overall', 'categoryRows', 'paymentRows'));
    }

    protected function amountExpr(): string
    {
        return 'COALESCE(contribution_amount, COALESCE(zakat_fitrah_amount,0)+COALESCE(zakat_mal_amount,0)+COALESCE(infaq_amount,0)+COALESCE(sodaqoh_amount,0))';
    }
}
