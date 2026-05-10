<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Community;
use App\Models\InitialVoucher;
use App\Models\Pic;
use App\Services\QurbanExportService;
use App\Services\QurbanPricingService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(
        protected QurbanExportService $exportService,
        protected QurbanPricingService $pricingService
    ) {
    }

    public function index()
    {
        $amountExpr = $this->amountExpression();

        $stats = [
            'total_claims'       => Claim::count(),
            'total_amount'       => (float) Claim::selectRaw("COALESCE(SUM({$amountExpr}), 0) AS total")->value('total'),
            'total_certificates' => Claim::whereNotNull('certificate_generated_at')->count(),
            'total_vouchers'     => InitialVoucher::count(),
        ];

        $pics        = Pic::orderBy('name')->get();
        $communities = Community::orderBy('name')->get();
        $categories  = $this->pricingService->options();

        return view('admin.exports.index', compact('stats', 'pics', 'communities', 'categories'));
    }

    public function claims(Request $request)
    {
        $query = Claim::with(['initialVoucher.pic', 'initialVoucher.community', 'pic']);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('pic_id')) {
            $picId = $request->pic_id;
            $query->where(function ($q) use ($picId) {
                $q->where('pic_id', $picId)
                  ->orWhereHas('initialVoucher', fn ($vq) => $vq->where('assigned_pic_id', $picId));
            });
        }
        if ($request->filled('community_id')) {
            $query->whereHas('initialVoucher', fn ($vq) => $vq->where('community_id', $request->community_id));
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
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('instagram_username', 'like', "%{$s}%")
                  ->orWhere('public_token', 'like', "%{$s}%")
                  ->orWhereHas('initialVoucher', fn ($vq) => $vq->where('code', 'like', "%{$s}%"));
            });
        }

        $claims   = $query->orderBy('created_at', 'desc')->get();
        $filename = 'kontribusi_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($claims) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->exportService->claimHeadings());
            foreach ($claims as $claim) {
                fputcsv($handle, $this->exportService->claimRow($claim));
            }
            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function redeems()
    {
        return redirect()->route('admin.exports.index')
            ->with('error', 'Export redemption merchant sudah dipensiunkan pada rebuild kurban.');
    }

    public function vouchers(Request $request)
    {
        $query = InitialVoucher::with(['batch', 'pic', 'community', 'claim']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('community_id')) {
            $query->where('community_id', $request->community_id);
        }

        $vouchers = $query->orderBy('created_at', 'desc')->get();
        $filename = 'vouchers_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($vouchers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->exportService->voucherHeadings());
            foreach ($vouchers as $voucher) {
                fputcsv($handle, $this->exportService->voucherRow($voucher));
            }
            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    protected function amountExpression(): string
    {
        return 'COALESCE(contribution_amount, COALESCE(zakat_fitrah_amount, 0) + COALESCE(zakat_mal_amount, 0) + COALESCE(infaq_amount, 0) + COALESCE(sodaqoh_amount, 0))';
    }
}
