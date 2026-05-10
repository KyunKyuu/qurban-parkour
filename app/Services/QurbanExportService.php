<?php

namespace App\Services;

use App\Models\Claim;
use App\Models\InitialVoucher;

class QurbanExportService
{
    public function claimHeadings(): array
    {
        return [
            'ID',
            'Source',
            'Kode Voucher',
            'Public Token',
            'Nama',
            'Email',
            'No. HP',
            'Instagram',
            'Komunitas',
            'PIC',
            'Kategori',
            'Nominal',
            'Metode Bayar',
            'Tujuan Transfer',
            'Verifikasi',
            'Catatan Verifikasi',
            'Sertifikat',
            'Sertifikat Generated At',
            'Tgl Kontribusi',
        ];
    }

    public function claimRow(Claim $claim): array
    {
        $source = $claim->initial_voucher_id ? 'Voucher PIC' : 'Direct Web';
        $pic    = $claim->pic?->name ?? $claim->initialVoucher?->pic?->name ?? '';
        $community = $claim->initialVoucher?->community?->name ?? '';

        return [
            $claim->id,
            $source,
            $claim->initialVoucher?->code ?? '',
            $claim->public_token ?? '',
            $claim->name,
            $claim->email,
            $claim->phone ?? '',
            $claim->instagram_username ? '@' . ltrim($claim->instagram_username, '@') : '',
            $community,
            $pic,
            $claim->display_category_label,
            (float) $claim->total_donation_amount,
            $claim->payment_method ?? 'cash',
            $claim->transfer_destination ?? '',
            $claim->verification_status ?? 'PENDING',
            $claim->verification_note ?? '',
            $claim->certificate_generated_at ? 'SUDAH' : 'BELUM',
            $claim->certificate_generated_at?->format('Y-m-d H:i:s') ?? '',
            $claim->created_at?->format('Y-m-d H:i:s') ?? '',
        ];
    }

    public function voucherHeadings(): array
    {
        return [
            'ID',
            'Kode',
            'Status',
            'Komunitas',
            'PIC',
            'Peserta',
            'Kategori',
            'Nominal',
            'Status Sertifikat',
            'Tgl Claim',
            'Tgl Dibuat',
        ];
    }

    public function voucherRow(InitialVoucher $voucher): array
    {
        return [
            $voucher->id,
            $voucher->code,
            $voucher->status,
            $voucher->community?->name ?? '-',
            $voucher->pic?->name ?? '-',
            $voucher->claim?->name ?? '-',
            $voucher->claim?->display_category_label ?? '-',
            (float) ($voucher->claim?->total_donation_amount ?? 0),
            $voucher->claim?->certificate_generated_at ? 'SUDAH' : 'BELUM',
            $voucher->claimed_at?->format('Y-m-d H:i:s') ?? '-',
            $voucher->created_at?->format('Y-m-d H:i:s') ?? '-',
        ];
    }
}
