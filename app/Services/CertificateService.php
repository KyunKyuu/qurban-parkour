<?php

namespace App\Services;

use App\Models\Claim;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateService
{
    private string $fontRegular;
    private string $fontBold;

    // Proportional Y-center of the white box (tweak if text is off)
    private const BOX_CENTER_Y = 0.425;
    // Maximum text width relative to image width
    private const BOX_MAX_W    = 0.80;

    public function __construct()
    {
        $this->fontRegular = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $this->fontBold    = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
    }

    public function generateForClaim(Claim $claim): Claim
    {
        $claim->loadMissing(['initialVoucher.pic', 'pic']);

        $filename = sprintf('sertifikat-%s-%s.png', $claim->id, Str::slug($claim->name ?: 'peserta'));
        $path     = 'certificates/' . $filename;

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

    protected function render(Claim $claim): string
    {
        $img = $this->loadTemplate();
        $w   = imagesx($img);
        $h   = imagesy($img);

        $boxCX   = $w * 0.50;
        $boxCY   = $h * self::BOX_CENTER_Y;
        $boxMaxW = (int) ($w * self::BOX_MAX_W);

        $black    = imagecolorallocate($img, 25,  25,  25);
        $darkGray = imagecolorallocate($img, 85,  85,  85);

        $fR = $this->fontRegular;
        $fB = $this->fontBold;

        $hasIg = !empty($claim->instagram_username);

        if ($hasIg) {
            $ig     = '@' . ltrim($claim->instagram_username, '@');
            $nameCY = (int) ($boxCY - $h * 0.022);
            $igCY   = (int) ($boxCY + $h * 0.018);
            $this->textCenterWrap($img, $fB, 68, $boxCX, $nameCY, $boxMaxW, $black, $claim->name);
            $this->textCenter($img, $fR, 36, $boxCX, $igCY, $darkGray, $ig);
        } else {
            $this->textCenterWrap($img, $fB, 72, $boxCX, (int) $boxCY, $boxMaxW, $black, $claim->name);
        }

        ob_start();
        imagepng($img, null, 7);
        return ob_get_clean();
    }

    protected function loadTemplate(): \GdImage
    {
        foreach (['jpg', 'jpeg', 'png'] as $ext) {
            $path = public_path("images/certificate/template.{$ext}");
            if (file_exists($path)) {
                if ($ext === 'png') {
                    $img = imagecreatefrompng($path);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);
                } else {
                    $img = imagecreatefromjpeg($path);
                }
                return $img;
            }
        }

        // Fallback: generate plain background so the service never breaks
        return $this->makeFallbackBackground();
    }

    protected function makeFallbackBackground(): \GdImage
    {
        $w   = 1080;
        $h   = 1920;
        $img = imagecreatetruecolor($w, $h);

        for ($y = 0; $y < $h; $y++) {
            $t = $y / ($h - 1);
            $c = imagecolorallocate($img,
                (int) round(26 + (13 - 26) * $t),
                (int) round(54 + (31 - 54) * $t),
                (int) round(40 + (21 - 40) * $t)
            );
            imageline($img, 0, $y, $w - 1, $y, $c);
        }

        return $img;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function textCenter(
        \GdImage $img, string $font, int $size,
        float $cx, float $cy, int $color, string $text
    ): void {
        if ($text === '') return;

        $bbox = imagettfbbox($size, 0, $font, $text);
        $tw   = abs($bbox[4] - $bbox[0]);
        $th   = abs($bbox[7] - $bbox[1]);

        imagettftext($img, $size, 0,
            (int) ($cx - $tw / 2),
            (int) ($cy + $th / 2),
            $color, $font, $text
        );
    }

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

        $words = explode(' ', $text);
        $n     = count($words);

        if ($n === 1 || $size <= 36) {
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
            $this->textCenterWrap($img, $font, (int) ($size * 0.85), $cx, $cy, $maxW, $color, $text);
            return;
        }

        $gap = (int) ($size * 1.3);
        $this->textCenter($img, $font, $size, $cx, (int) ($cy - $gap / 2), $color, $l1);
        $this->textCenter($img, $font, $size, $cx, (int) ($cy + $gap / 2), $color, $l2);
    }
}
