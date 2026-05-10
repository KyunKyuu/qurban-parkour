<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\InitialVoucher;
use App\Models\Pic;
use App\Services\CertificateService;
use App\Services\QurbanExportService;
use App\Services\QurbanPricingService;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder as QrEncoder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use ZipArchive;

class DashboardController extends Controller
{
    public function __construct(
        protected CertificateService $certificateService,
        protected QurbanExportService $exportService,
        protected QurbanPricingService $pricingService,
    ) {
    }

    public function index(Request $request)
    {
        $pic = auth()->user()->pic;
        if (!$pic) {
            abort(403, 'User is not associated with a PIC account');
        }

        $communities      = $pic->communities()->orderBy('name')->get();
        $activeCommunityId = $request->filled('community_id') ? (int) $request->community_id : null;
        $activeCommunity  = $activeCommunityId ? $communities->firstWhere('id', $activeCommunityId) : null;

        $baseQuery     = $this->claimsQuery($pic, $activeCommunityId);
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);

        $stats         = $this->summarize(clone $filteredQuery);
        $categoryStats = $this->buildCategoryStats((clone $filteredQuery)->get());
        $claims        = $filteredQuery->latest()->paginate(15)->withQueryString();
        $pricingOptions = $this->pricingService->options();

        return view('pic.dashboard', compact(
            'pic',
            'communities',
            'activeCommunityId',
            'activeCommunity',
            'stats',
            'claims',
            'categoryStats',
            'pricingOptions',
        ));
    }

    public function exportData(Request $request)
    {
        $pic = auth()->user()->pic;
        if (!$pic) {
            abort(403, 'User is not associated with a PIC account');
        }

        $activeCommunityId = $request->filled('community_id') ? (int) $request->community_id : null;
        $claims = $this->applyFilters($this->claimsQuery($pic, $activeCommunityId), $request)
            ->latest()
            ->get();

        $filename = 'kontribusi_pic_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($claims) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->exportService->claimHeadings());
            foreach ($claims as $claim) {
                fputcsv($handle, $this->exportService->claimRow($claim));
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportVouchersPdf(Request $request)
    {
        $pic = auth()->user()->pic;
        if (!$pic) {
            abort(403, 'User is not associated with a PIC account');
        }

        $communityId = $request->integer('community_id');
        $community   = $pic->communities()->findOrFail($communityId);

        $vouchers = InitialVoucher::where('assigned_pic_id', $pic->id)
            ->where('community_id', $communityId)
            ->orderBy('code')
            ->get();

        if ($vouchers->isEmpty()) {
            return back()->with('error', 'Tidak ada voucher untuk komunitas ini.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'pic_vouchers_') . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($vouchers as $voucher) {
            $claimUrl     = rtrim(config('app.url'), '/') . '/claim/' . $voucher->code;
            $voucher->qr_png = $this->generateQrPng($claimUrl);

            $pdf = Pdf::loadView('pic.print.single-voucher', ['voucher' => $voucher]);
            $pdf->setPaper('a4', 'landscape');
            $zip->addFromString('voucher-' . $voucher->code . '.pdf', $pdf->output());
        }

        $zip->close();

        $filename = 'vouchers-' . Str::slug($community->name) . '-' . now()->format('Y-m-d') . '.zip';

        return response()->download($zipPath, $filename, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    protected function generateQrPng(string $text, int $targetPx = 300): string
    {
        $qr     = QrEncoder::encode($text, ErrorCorrectionLevel::L(), 'UTF-8');
        $matrix = $qr->getMatrix();
        $size   = $matrix->getWidth();
        $scale  = (int) max(1, floor($targetPx / $size));
        $margin = $scale * 2;
        $imgPx  = $size * $scale + $margin * 2;

        $img   = imagecreatetruecolor($imgPx, $imgPx);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);

        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if ($matrix->get($x, $y) !== 0) {
                    $px = $margin + $x * $scale;
                    $py = $margin + $y * $scale;
                    imagefilledrectangle($img, $px, $py, $px + $scale - 1, $py + $scale - 1, $black);
                }
            }
        }

        ob_start();
        imagepng($img);
        $data = ob_get_clean();

        return 'data:image/png;base64,' . base64_encode($data);
    }

    public function downloadCertificate($id)
    {
        $pic = auth()->user()->pic;
        if (!$pic) {
            abort(403, 'User is not associated with a PIC account');
        }

        $claim = $this->claimsQuery($pic)->findOrFail($id);

        return $this->certificateService->download($claim);
    }

    // ─────────────────────────────────────────────────────────────────────────

    protected function claimsQuery(Pic $pic, ?int $communityId = null)
    {
        if ($communityId) {
            return Claim::query()
                ->with(['initialVoucher', 'pic'])
                ->whereHas('initialVoucher', function ($q) use ($pic, $communityId) {
                    $q->where('assigned_pic_id', $pic->id)
                      ->where('community_id', $communityId);
                });
        }

        return Claim::query()
            ->with(['initialVoucher', 'pic'])
            ->where(function ($q) use ($pic) {
                $q->where('pic_id', $pic->id)
                  ->orWhereHas('initialVoucher', fn ($vq) => $vq->where('assigned_pic_id', $pic->id));
            });
    }

    protected function applyFilters($query, Request $request)
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
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
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('instagram_username', 'like', "%{$s}%")
                  ->orWhereHas('initialVoucher', fn ($vq) => $vq->where('code', 'like', "%{$s}%"));
            });
        }

        return $query;
    }

    protected function summarize($query): array
    {
        return [
            'total_claims'           => (clone $query)->count(),
            'total_amount'           => (float) (clone $query)->sum('contribution_amount'),
            'certificates_generated' => (clone $query)->whereNotNull('certificate_generated_at')->count(),
        ];
    }

    protected function buildCategoryStats($claims): \Illuminate\Support\Collection
    {
        return $claims
            ->groupBy(fn (Claim $c) => $c->display_category_label)
            ->map(fn ($group, $label) => [
                'label'        => $label,
                'total_claims' => $group->count(),
                'total_amount' => $group->sum(fn (Claim $c) => $c->total_donation_amount),
            ])
            ->values();
    }
}
