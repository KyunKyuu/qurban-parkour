<?php

namespace App\Http\Controllers\Pic;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Community;
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
        if (!$pic) abort(403, 'User is not associated with a PIC account');

        if ($pic->isKomunitas()) {
            return $this->indexKomunitas($request, $pic);
        }

        return $this->indexKasie($request, $pic);
    }

    // ── PIC Kasie ─────────────────────────────────────────────────────────────

    protected function indexKasie(Request $request, Pic $pic)
    {
        $communities       = $pic->communities()->orderBy('name')->get();
        $activeCommunityId = $request->filled('community_id') ? (int) $request->community_id : null;
        $activeCommunity   = $activeCommunityId ? $communities->firstWhere('id', $activeCommunityId) : null;
        $langsung          = $request->boolean('langsung');

        $hasDirectVouchers = InitialVoucher::where('assigned_pic_id', $pic->id)
            ->whereNull('community_id')
            ->exists();

        $baseQuery     = $this->claimsQueryKasie($pic, $activeCommunityId, $langsung);
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);

        $stats         = $this->summarize(clone $filteredQuery);
        $categoryStats = $this->buildCategoryStats((clone $filteredQuery)->get());
        $claims        = $filteredQuery->latest()->paginate(15)->withQueryString();
        $pricingOptions = $this->pricingService->options();

        return view('pic.dashboard', compact(
            'pic', 'communities', 'activeCommunityId', 'activeCommunity',
            'stats', 'claims', 'categoryStats', 'pricingOptions',
            'hasDirectVouchers', 'langsung',
        ));
    }

    // ── PIC Komunitas ─────────────────────────────────────────────────────────

    protected function indexKomunitas(Request $request, Pic $pic)
    {
        $community = Community::where('pic_komunitas_id', $pic->id)->first();

        if (!$community) {
            return view('pic.dashboard-komunitas', [
                'pic'           => $pic,
                'community'     => null,
                'stats'         => ['total_claims' => 0, 'total_amount' => 0, 'certificates_generated' => 0],
                'claims'        => collect(),
                'categoryStats' => collect(),
                'pricingOptions' => $this->pricingService->options(),
            ]);
        }

        $baseQuery     = $this->claimsQueryKomunitas($community);
        $filteredQuery = $this->applyFilters(clone $baseQuery, $request);

        $stats         = $this->summarize(clone $filteredQuery);
        $categoryStats = $this->buildCategoryStats((clone $filteredQuery)->get());
        $claims        = $filteredQuery->latest()->paginate(20)->withQueryString();
        $pricingOptions = $this->pricingService->options();

        return view('pic.dashboard-komunitas', compact(
            'pic', 'community', 'stats', 'claims', 'categoryStats', 'pricingOptions',
        ));
    }

    // ── Export CSV (Kasie) ────────────────────────────────────────────────────

    public function exportData(Request $request)
    {
        $pic = auth()->user()->pic;
        if (!$pic) abort(403);

        $claims = $this->applyFilters($this->getBaseQuery($pic, $request), $request)
            ->latest()->get();

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

    // ── Export Excel (Kasie & Komunitas) ──────────────────────────────────────

    public function exportExcel(Request $request)
    {
        $pic = auth()->user()->pic;
        if (!$pic) abort(403);

        $claims = $this->applyFilters($this->getBaseQuery($pic, $request), $request)
            ->latest()->get();

        $headings = $this->exportService->claimHeadings();
        $filename = 'kontribusi_' . Str::slug($pic->name) . '_' . now()->format('Y-m-d') . '.xls';

        return response()->streamDownload(function () use ($claims, $headings) {
            echo '<html><head><meta charset="UTF-8"></head><body>';
            echo '<table border="1" style="border-collapse:collapse">';
            echo '<tr>';
            foreach ($headings as $h) {
                echo '<th style="background:#1a3628;color:#fff;padding:6px 10px">' . htmlspecialchars($h) . '</th>';
            }
            echo '</tr>';
            foreach ($claims as $claim) {
                echo '<tr>';
                foreach ($this->exportService->claimRow($claim) as $cell) {
                    echo '<td style="padding:5px 10px">' . htmlspecialchars((string) $cell) . '</td>';
                }
                echo '</tr>';
            }
            echo '</table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
        ]);
    }

    // ── Export Voucher PDF (Kasie only) ───────────────────────────────────────

    public function exportVouchersPdf(Request $request)
    {
        $pic = auth()->user()->pic;
        if (!$pic) abort(403);

        if ($request->boolean('langsung')) {
            $vouchers = InitialVoucher::where('assigned_pic_id', $pic->id)
                ->whereNull('community_id')
                ->orderBy('code')
                ->get();

            if ($vouchers->isEmpty()) {
                return back()->with('error', 'Tidak ada voucher langsung yang di-assign ke Anda.');
            }

            $filename = 'vouchers-langsung-' . Str::slug($pic->name) . '-' . now()->format('Y-m-d') . '.zip';
        } else {
            $communityId = $request->integer('community_id');
            $community   = $pic->communities()->findOrFail($communityId);

            $vouchers = InitialVoucher::where('community_id', $communityId)
                ->orderBy('code')
                ->get();

            if ($vouchers->isEmpty()) {
                return back()->with('error', 'Tidak ada voucher untuk komunitas ini.');
            }

            $filename = 'vouchers-' . Str::slug($community->name) . '-' . now()->format('Y-m-d') . '.zip';
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'pic_vouchers_') . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($vouchers as $i => $voucher) {
            $claimUrl        = rtrim(config('app.url'), '/') . '/claim/' . $voucher->code;
            $voucher->qr_png = $this->generateQrPng($claimUrl);

            $pdf = Pdf::loadView('pic.print.single-voucher', ['voucher' => $voucher]);
            $pdf->setPaper('a4', 'landscape');
            $zip->addFromString(($i + 1) . '-' . $voucher->code . '.pdf', $pdf->output());
        }

        $zip->close();

        return response()->download($zipPath, $filename, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    // ── Export Voucher PDF (Komunitas — no community selection needed) ────────

    public function exportVouchersPdfKomunitas(Request $request)
    {
        $pic = auth()->user()->pic;
        if (!$pic || !$pic->isKomunitas()) abort(403);

        $community = Community::where('pic_komunitas_id', $pic->id)->first();
        if (!$community) return back()->with('error', 'Anda belum ditugaskan ke komunitas manapun.');

        $vouchers = InitialVoucher::where('community_id', $community->id)->orderBy('code')->get();

        if ($vouchers->isEmpty()) {
            return back()->with('error', 'Tidak ada voucher untuk komunitas ini.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'pic_komunitas_vouchers_') . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($vouchers as $i => $voucher) {
            $claimUrl        = rtrim(config('app.url'), '/') . '/claim/' . $voucher->code;
            $voucher->qr_png = $this->generateQrPng($claimUrl);

            $pdf = Pdf::loadView('pic.print.single-voucher', ['voucher' => $voucher]);
            $pdf->setPaper('a4', 'landscape');
            $zip->addFromString(($i + 1) . '-' . $voucher->code . '.pdf', $pdf->output());
        }

        $zip->close();

        $filename = 'vouchers-' . Str::slug($community->name) . '-' . now()->format('Y-m-d') . '.zip';

        return response()->download($zipPath, $filename, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    // ── Download Certificate ──────────────────────────────────────────────────

    public function downloadCertificate($id)
    {
        $pic = auth()->user()->pic;
        if (!$pic) abort(403);

        $claim = $this->getBaseQuery($pic, request())->findOrFail($id);

        return $this->certificateService->download($claim);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function getBaseQuery(Pic $pic, Request $request)
    {
        if ($pic->isKomunitas()) {
            $community = Community::where('pic_komunitas_id', $pic->id)->first();
            return $community
                ? $this->claimsQueryKomunitas($community)
                : Claim::query()->whereNull('id');
        }

        $communityId = $request->filled('community_id') ? (int) $request->community_id : null;
        $langsung    = $request->boolean('langsung');
        return $this->claimsQueryKasie($pic, $communityId, $langsung);
    }

    protected function claimsQueryKasie(Pic $pic, ?int $communityId = null, bool $langsung = false)
    {
        if ($langsung) {
            return Claim::query()
                ->with(['initialVoucher', 'pic'])
                ->whereHas('initialVoucher', fn ($q) => $q
                    ->where('assigned_pic_id', $pic->id)
                    ->whereNull('community_id')
                );
        }

        if ($communityId) {
            return Claim::query()
                ->with(['initialVoucher', 'pic'])
                ->whereHas('initialVoucher', fn ($q) => $q->where('community_id', $communityId));
        }

        $communityIds = $pic->communities()->pluck('id');

        return Claim::query()
            ->with(['initialVoucher', 'pic'])
            ->whereHas('initialVoucher', fn ($q) => $q->where(function ($sub) use ($communityIds, $pic) {
                $sub->whereIn('community_id', $communityIds)
                    ->orWhere(fn ($d) => $d
                        ->where('assigned_pic_id', $pic->id)
                        ->whereNull('community_id')
                    );
            }));
    }

    protected function claimsQueryKomunitas(Community $community)
    {
        return Claim::query()
            ->with(['initialVoucher', 'pic'])
            ->whereHas('initialVoucher', fn ($q) => $q->where('community_id', $community->id));
    }

    protected function applyFilters($query, Request $request)
    {
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('category_type')) $query->where('category_type', $request->category_type);

        if ($request->certificate_status === 'generated') $query->whereNotNull('certificate_generated_at');
        if ($request->certificate_status === 'missing')   $query->whereNull('certificate_generated_at');

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
}
