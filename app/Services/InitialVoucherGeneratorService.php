<?php

namespace App\Services;

use App\Models\InitialVoucher;
use App\Models\VoucherBatch;
use App\Support\CodeGenerator;
use Illuminate\Support\Facades\DB;

class InitialVoucherGeneratorService
{
    public function generate(int $count, ?string $batchName, int $adminId): VoucherBatch
    {
        return DB::transaction(function () use ($count, $batchName, $adminId) {
            $batch = VoucherBatch::create([
                'name'                => $batchName ?? 'Batch ' . now()->format('Y-m-d H:i:s'),
                'generated_count'     => $count,
                'created_by_admin_id' => $adminId,
            ]);

            $vouchers = [];
            for ($i = 0; $i < $count; $i++) {
                $vouchers[] = [
                    'batch_id'   => $batch->id,
                    'code'       => $this->generateUniqueCode(),
                    'status'     => 'UNASSIGNED',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            InitialVoucher::insert($vouchers);

            return $batch;
        });
    }

    /**
     * Generate a unique code that doesn't exist in the database.
     *
     * @return string
     */
    protected function generateUniqueCode(): string
    {
        do {
            $code = CodeGenerator::make(14);
        } while (InitialVoucher::where('code', $code)->exists());

        return $code;
    }
}
