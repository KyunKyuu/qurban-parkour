<?php

namespace App\Services;

use App\Models\Claim;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateService
{
    private string $fontRegular;
    private string $fontBold;

    public function __construct()
    {
        // Use DejaVu fonts bundled with DomPDF — no extra package needed
        $this->fontRegular = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $this->fontBold    = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
    }

    public function generateForClaim(Claim $claim): Claim
    {
        $claim->loadMissing(['initialVoucher.pic', 'pic']);

        $filename = sprintf(
            'sertifikat-%s-%s.png',
            $claim->id,
            Str::slug($claim->name ?: 'peserta')
        );
        $path = 'certificates/' . $filename;

        Storage::disk('public')->put($path, $this->render($claim));

        $claim->forceFill([
            'certificate_path'         => $path,
            'certificate_generated_at' => now(),
        ])->save();

        return $claim->fresh(['initialVoucher.pic', 'pic']);
    }

    public function ensureGenerated(Claim $claim): Claim
    {
        if ($claim->certificate_path && Storage::disk('public')->exists($claim->certificate_path)) {
            return $claim->fresh(['initialVoucher.pic', 'pic']);
        }

        return $this->generateForClaim($claim);
    }

    public function download(Claim $claim)
    {
        $claim = $this->ensureGenerated($claim);

        return Storage::disk('public')->download(
            $claim->certificate_path,
            $claim->certificate_filename
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Image rendering
    // ─────────────────────────────────────────────────────────────────────────

    protected function render(Claim $claim): string
    {
        $w = 1080;
        $h = 1920;

        $img = imagecreatetruecolor($w, $h);

        // Gradient background: #1a3628 (top) → #0d1f15 (bottom)
        for ($y = 0; $y < $h; $y++) {
            $t = $y / ($h - 1);
            $c = imagecolorallocate(
                $img,
                (int) round(26 + (13 - 26) * $t),
                (int) round(54 + (31 - 54) * $t),
                (int) round(40 + (21 - 40) * $t)
            );
            imageline($img, 0, $y, $w - 1, $y, $c);
        }

        // Palette
        $cream     = imagecolorallocate($img, 240, 235, 224); // #f0ebe0
        $amber     = imagecolorallocate($img, 232, 162,  62); // #e8a23e
        $softGreen = imagecolorallocate($img, 168, 197, 176); // #a8c5b0
        $white     = imagecolorallocate($img, 255, 255, 255);
        $dim       = imagecolorallocate($img, 40,  74,  56);  // subtle lines

        $fR = $this->fontRegular;
        $fB = $this->fontBold;

        $campaign = config('qurban.campaign_name', 'Kurban Berdaya 1447 H');

        // ── Decorative rings — top-right corner ──────────────────────────────
        foreach ([560, 420, 280] as $diameter) {
            imageellipse($img, $w + 60, -60, $diameter, $diameter, $dim);
        }
        // Bottom-left corner
        foreach ([480, 340] as $diameter) {
            imageellipse($img, -60, $h + 60, $diameter, $diameter, $dim);
        }

        // ── Top: kicker + rule ───────────────────────────────────────────────
        $this->textCenter($img, $fR, 26, $w / 2, 145, $softGreen, strtoupper($campaign));
        imageline($img, 80, 180, $w - 80, 180, $dim);

        // ── Hero text ────────────────────────────────────────────────────────
        $this->textCenter($img, $fB, 104, $w / 2, 360, $amber, 'Terima Kasih');
        $this->textCenter($img, $fR,  38, $w / 2, 468, $cream, 'atas kontribusi Anda dalam');
        $this->textCenter($img, $fB,  44, $w / 2, 546, $cream, $campaign);

        // ── Rule ─────────────────────────────────────────────────────────────
        imageline($img, 140, 610, $w - 140, 610, $dim);

        // ── Recipient block ──────────────────────────────────────────────────
        $this->textCenter($img, $fR, 30, $w / 2, 740, $softGreen, 'Diberikan kepada');

        // Name — wrap to two lines if too wide, shrink if still too wide
        $this->textCenterWrap($img, $fB, 82, $w / 2, 890, 880, $white, $claim->name);

        // Category pill
        $categoryLabel = $claim->display_category_label;
        $this->textCenter($img, $fR, 38, $w / 2, 1054, $amber, $categoryLabel);

        // ── Rule ─────────────────────────────────────────────────────────────
        imageline($img, 140, 1120, $w - 140, 1120, $dim);

        // ── Date ─────────────────────────────────────────────────────────────
        $date = ($claim->certificate_generated_at ?? now())->format('d F Y');
        $this->textCenter($img, $fR, 28, $w / 2, 1196, $softGreen, $date);

        // ── Spacing — large white space keeps it airy for IG Story ───────────

        // ── Bottom message ───────────────────────────────────────────────────
        imageline($img, 80, $h - 264, $w - 80, $h - 264, $dim);
        $this->textCenter($img, $fR, 29, $w / 2, $h - 212, $cream, 'Semoga ikhtiar ini menjadi amal yang diterima');
        $this->textCenter($img, $fR, 29, $w / 2, $h - 162, $cream, 'dan membawa keberkahan bagi seluruh umat.');
        $this->textCenter($img, $fR, 23, $w / 2, $h -  88, $softGreen, strtoupper($campaign));

        // ── Export PNG ───────────────────────────────────────────────────────
        ob_start();
        imagepng($img, null, 7);
        $data = ob_get_clean();

        return $data;
    }

    // Draw text horizontally centered around ($cx, $cy) — cy is visual center
    protected function textCenter(
        \GdImage $img, string $font, int $size,
        float $cx, float $cy, int $color, string $text
    ): void {
        if ($text === '') return;

        $bbox = imagettfbbox($size, 0, $font, $text);
        $tw   = abs($bbox[4] - $bbox[0]);
        $th   = abs($bbox[7] - $bbox[1]);

        imagettftext(
            $img, $size, 0,
            (int) ($cx - $tw / 2),
            (int) ($cy + $th / 2),   // baseline = cy + ascender/2
            $color, $font, $text
        );
    }

    // Draw text centered, wrapping to 2 lines if wider than $maxW.
    // Shrinks font iteratively if still too wide after wrapping.
    protected function textCenterWrap(
        \GdImage $img, string $font, int $size,
        float $cx, float $cy, int $maxW,
        int $color, string $text
    ): void {
        if ($text === '') return;

        $bbox = imagettfbbox($size, 0, $font, $text);
        $tw   = abs($bbox[4] - $bbox[0]);

        if ($tw <= $maxW) {
            $this->textCenter($img, $font, $size, $cx, (int) $cy, $color, $text);
            return;
        }

        // Try to find the best split into 2 lines
        $words = explode(' ', $text);
        $n     = count($words);

        if ($n === 1 || $size <= 40) {
            // Can't split or too small — just draw at reduced size
            $this->textCenterWrap($img, $font, (int) ($size * 0.85), $cx, $cy, $maxW, $color, $text);
            return;
        }

        $bestSplit = 1;
        $bestScore = PHP_INT_MAX;

        for ($i = 1; $i < $n; $i++) {
            $l1 = implode(' ', array_slice($words, 0, $i));
            $l2 = implode(' ', array_slice($words, $i));
            $b1 = imagettfbbox($size, 0, $font, $l1);
            $b2 = imagettfbbox($size, 0, $font, $l2);
            $w1 = abs($b1[4] - $b1[0]);
            $w2 = abs($b2[4] - $b2[0]);
            $score = abs($w1 - $w2) + max(0, max($w1, $w2) - $maxW) * 10;

            if ($score < $bestScore) {
                $bestScore = $score;
                $bestSplit = $i;
            }
        }

        $l1 = implode(' ', array_slice($words, 0, $bestSplit));
        $l2 = implode(' ', array_slice($words, $bestSplit));
        $b1 = imagettfbbox($size, 0, $font, $l1);
        $b2 = imagettfbbox($size, 0, $font, $l2);

        if (max(abs($b1[4] - $b1[0]), abs($b2[4] - $b2[0])) > $maxW) {
            // Widest line still overflows — shrink and retry
            $this->textCenterWrap($img, $font, (int) ($size * 0.85), $cx, $cy, $maxW, $color, $text);
            return;
        }

        $gap = (int) ($size * 1.25);
        $this->textCenter($img, $font, $size, $cx, (int) ($cy - $gap / 2), $color, $l1);
        $this->textCenter($img, $font, $size, $cx, (int) ($cy + $gap / 2), $color, $l2);
    }
}
