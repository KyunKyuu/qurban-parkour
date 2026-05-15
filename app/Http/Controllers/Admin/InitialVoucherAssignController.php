<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pic;
use App\Models\VoucherBatch;
use App\Services\InitialVoucherAssignService;
use Illuminate\Http\Request;

class InitialVoucherAssignController extends Controller
{
    protected $assignService;

    public function __construct(InitialVoucherAssignService $assignService)
    {
        $this->assignService = $assignService;
    }

    public function create()
    {
        $picKasie = Pic::where('is_active', true)
            ->where('pic_type', '!=', 'komunitas')
            ->orderBy('name')
            ->get();

        $picKomunitas = Pic::where('is_active', true)
            ->where('pic_type', 'komunitas')
            ->with('communityAsPicKomunitas')
            ->orderBy('name')
            ->get();

        $batches        = VoucherBatch::latest()->get();
        $availableCount = $this->assignService->getAvailableCount();

        return view('admin.vouchers.assign', compact('picKasie', 'picKomunitas', 'batches', 'availableCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pic_id'   => 'required|exists:pics,id',
            'qty'      => 'required|integer|min:1',
            'batch_id' => 'nullable|exists:voucher_batches,id',
        ]);

        try {
            $pic = Pic::with('communityAsPicKomunitas')->findOrFail($validated['pic_id']);

            $count = $this->assignService->assign(
                $validated['pic_id'],
                $validated['qty'],
                $validated['batch_id'] ?? null,
            );

            if ($pic->isKomunitas()) {
                $detail = $pic->communityAsPicKomunitas
                    ? ' (komunitas: ' . $pic->communityAsPicKomunitas->name . ')'
                    : '';
            } else {
                $detail = ' (langsung ke kasie)';
            }

            return redirect()
                ->route('admin.vouchers.assign')
                ->with('success', "Berhasil assign {$count} voucher ke {$pic->name}{$detail}");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal assign voucher: ' . $e->getMessage());
        }
    }
}
