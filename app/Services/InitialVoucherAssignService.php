<?php

namespace App\Services;

use App\Models\InitialVoucher;
use App\Models\Pic;
use Illuminate\Support\Facades\DB;

class InitialVoucherAssignService
{
    /**
     * Assign vouchers to a PIC Komunitas.
     * community_id is auto-resolved from the PIC Komunitas's linked community.
     */
    public function assign(int $picId, int $qty, ?int $batchId = null): int
    {
        return DB::transaction(function () use ($picId, $qty, $batchId) {
            $pic = Pic::with('communityAsPicKomunitas')->findOrFail($picId);

            if (!$pic->is_active) {
                throw new \Exception('PIC tidak aktif.');
            }

            if (!$pic->isKomunitas()) {
                throw new \Exception('Voucher hanya bisa di-assign ke PIC Komunitas.');
            }

            // Auto-resolve community from PIC Komunitas's linked community
            $communityId = $pic->communityAsPicKomunitas?->id;

            $query = InitialVoucher::where('status', 'UNASSIGNED');

            if ($batchId) {
                $query->where('batch_id', $batchId);
            }

            $vouchers = $query->limit($qty)->get();

            if ($vouchers->count() < $qty) {
                throw new \Exception(
                    'Stok voucher tidak cukup. Tersedia: ' . $vouchers->count()
                );
            }

            InitialVoucher::whereIn('id', $vouchers->pluck('id')->toArray())->update([
                'status'          => 'ASSIGNED',
                'assigned_pic_id' => $picId,
                'community_id'    => $communityId,
                'updated_at'      => now(),
            ]);

            return $vouchers->count();
        });
    }

    public function getAvailableCount(?int $batchId = null): int
    {
        $query = InitialVoucher::where('status', 'UNASSIGNED');

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        return $query->count();
    }
}
