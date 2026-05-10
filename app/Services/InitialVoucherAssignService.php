<?php

namespace App\Services;

use App\Models\InitialVoucher;
use App\Models\Pic;
use Illuminate\Support\Facades\DB;

class InitialVoucherAssignService
{
    /**
     * Assign vouchers to a PIC.
     *
     * @param int $picId ID of the PIC
     * @param int $qty Number of vouchers to assign
     * @param int|null $batchId Optional batch ID to filter vouchers
     * @return int Number of vouchers assigned
     * @throws \Exception
     */
    public function assign(int $picId, int $qty, ?int $batchId = null, ?int $communityId = null): int
    {
        return DB::transaction(function () use ($picId, $qty, $batchId, $communityId) {
            $pic = Pic::findOrFail($picId);
            if (!$pic->is_active) {
                throw new \Exception('PIC is not active');
            }

            // Pastikan community milik PIC yang dipilih
            if ($communityId !== null) {
                $communityBelongsToPic = $pic->communities()->where('id', $communityId)->exists();
                if (!$communityBelongsToPic) {
                    throw new \Exception('Komunitas tidak ditemukan untuk PIC ini.');
                }
            }

            $query = InitialVoucher::where('status', 'UNASSIGNED');

            if ($batchId) {
                $query->where('batch_id', $batchId);
            }

            $vouchers = $query->limit($qty)->get();

            if ($vouchers->count() < $qty) {
                throw new \Exception('Not enough unassigned vouchers available. Available: ' . $vouchers->count());
            }

            $voucherIds = $vouchers->pluck('id')->toArray();

            InitialVoucher::whereIn('id', $voucherIds)->update([
                'status'          => 'ASSIGNED',
                'assigned_pic_id' => $picId,
                'community_id'    => $communityId,
                'updated_at'      => now(),
            ]);

            return count($voucherIds);
        });
    }

    /**
     * Get count of available unassigned vouchers.
     *
     * @param int|null $batchId Optional batch ID to filter
     * @return int
     */
    public function getAvailableCount(?int $batchId = null): int
    {
        $query = InitialVoucher::where('status', 'UNASSIGNED');
        
        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        return $query->count();
    }
}
