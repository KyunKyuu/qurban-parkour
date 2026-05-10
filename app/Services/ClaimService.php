<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\InitialVoucher;
use App\Models\Pic;
use App\Support\CodeGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClaimService
{
    public function __construct(
        protected QurbanPricingService $pricingService,
        protected CertificateService $certificateService
    ) {
    }

    public function validateVoucherForClaim(string $code): InitialVoucher
    {
        $voucher = InitialVoucher::with('pic')
            ->where('code', $code)
            ->first();

        if (!$voucher) {
            throw ValidationException::withMessages([
                'code' => 'Kode kontribusi tidak ditemukan.',
            ]);
        }

        if ($voucher->status !== 'ASSIGNED') {
            $message = match ($voucher->status) {
                'UNASSIGNED' => 'Kode belum di-assign ke PIC.',
                'CLAIMED' => 'Kode sudah pernah dipakai.',
                'VOID' => 'Kode sudah tidak berlaku.',
                default => 'Kode tidak dapat dipakai.',
            };

            throw ValidationException::withMessages([
                'code' => $message,
            ]);
        }

        if (!$voucher->assigned_pic_id) {
            throw ValidationException::withMessages([
                'code' => 'Kode belum di-assign ke PIC.',
            ]);
        }

        return $voucher;
    }

    public function processClaim(array $payload, ?string $code = null): Claim
    {
        $claim = DB::transaction(function () use ($payload, $code) {
            $voucher = $code
                ? $this->lockVoucherForContribution($code, $payload['pic_id'] ?? null)
                : $this->createDirectVoucher((int) ($payload['pic_id'] ?? 0));

            $selection = $this->pricingService->resolveSelection(
                $payload['category_type'],
                isset($payload['contribution_amount']) ? (float) $payload['contribution_amount'] : null,
                $payload['patungan_target'] ?? null
            );

            $isTransfer = ($payload['payment_method'] ?? 'cash') === 'transfer';

            $claim = Claim::create([
                'initial_voucher_id' => $voucher->id,
                'pic_id' => $voucher->assigned_pic_id,
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'category_type' => $selection['category_type'],
                'category_label' => $selection['category_label'],
                'patungan_target' => $selection['patungan_target'],
                'unit_price_snapshot' => $selection['unit_price_snapshot'],
                'contribution_amount' => $selection['contribution_amount'],
                'instagram_username' => $this->normalizeInstagram($payload['instagram_username'] ?? null),
                'subsidy_amount' => $selection['subsidy_amount'],
                'zakat_fitrah_amount' => 0,
                'zakat_mal_amount' => 0,
                'infaq_amount' => 0,
                'sodaqoh_amount' => 0,
                'payment_method' => $payload['payment_method'] ?? 'cash',
                'transfer_destination' => $isTransfer ? ($payload['transfer_destination'] ?? null) : null,
                'transfer_proof_path' => $isTransfer ? ($payload['transfer_proof_path'] ?? null) : null,
                'public_token' => $this->generateUniquePublicToken(),
                'verification_status' => $isTransfer ? 'PENDING' : 'VERIFIED',
                'verified_at' => $isTransfer ? null : now(),
            ]);

            $voucher->update([
                'status' => 'CLAIMED',
                'claimed_at' => now(),
            ]);

            return $claim;
        });

        return $this->certificateService->ensureGenerated($claim);
    }

    protected function generateUniquePublicToken(): string
    {
        do {
            $token = CodeGenerator::makeToken(32);
        } while (Claim::where('public_token', $token)->exists());

        return $token;
    }

    public function getClaimByToken(string $token): ?Claim
    {
        return Claim::with([
            'initialVoucher.pic',
            'pic',
        ])->where('public_token', $token)->first();
    }

    public function downloadCertificate(Claim $claim)
    {
        return $this->certificateService->download($claim);
    }

    protected function lockVoucherForContribution(string $code, ?int $picId = null): InitialVoucher
    {
        $voucher = InitialVoucher::where('code', $code)
            ->lockForUpdate()
            ->first();

        if (!$voucher || $voucher->status !== 'ASSIGNED') {
            throw ValidationException::withMessages([
                'code' => 'Kode kontribusi tidak dapat dipakai. Mungkin sudah digunakan oleh peserta lain.',
            ]);
        }

        if (!$voucher->assigned_pic_id) {
            throw ValidationException::withMessages([
                'code' => 'Kode kontribusi belum terhubung ke PIC.',
            ]);
        }

        if ($picId && (int) $voucher->assigned_pic_id !== (int) $picId) {
            throw ValidationException::withMessages([
                'pic_id' => 'PIC yang dipilih tidak sesuai dengan kode kontribusi.',
            ]);
        }

        return $voucher;
    }

    protected function createDirectVoucher(int $picId = 0): InitialVoucher
    {
        $pic = $picId > 0
            ? Pic::find($picId)
            : $this->resolveDefaultPic();

        if (!$pic) {
            throw ValidationException::withMessages([
                'pic_id' => 'PIC default untuk web belum tersedia.',
            ]);
        }

        return InitialVoucher::create([
            'batch_id' => null,
            'code' => $this->generateUniqueVoucherCode(),
            'status' => 'ASSIGNED',
            'assigned_pic_id' => $pic->id,
        ]);
    }

    protected function resolveDefaultPic(): Pic
    {
        $defaultName = config('qurban.default_pic_name');
        $defaultEmail = config('qurban.default_pic_email');

        $pic = Pic::query()
            ->where(function ($query) use ($defaultName, $defaultEmail) {
                if ($defaultName) {
                    $query->where('name', $defaultName);
                }

                if ($defaultEmail) {
                    if ($defaultName) {
                        $query->orWhere('email', $defaultEmail);
                    } else {
                        $query->where('email', $defaultEmail);
                    }
                }
            })
            ->first();

        if ($pic) {
            return $pic;
        }

        return Pic::create([
            'name' => config('qurban.default_pic_label'),
            'code' => 'WEB-DIRECT',
            'email' => $defaultEmail,
            'password' => bcrypt(CodeGenerator::makeToken(16)),
            'is_active' => true,
        ]);
    }

    protected function generateUniqueVoucherCode(): string
    {
        do {
            $code = 'QB' . CodeGenerator::make(12);
        } while (InitialVoucher::where('code', $code)->exists());

        return $code;
    }

    protected function normalizeInstagram(?string $instagram): ?string
    {
        $instagram = trim((string) $instagram);

        return $instagram === '' ? null : ltrim($instagram, '@');
    }
}
