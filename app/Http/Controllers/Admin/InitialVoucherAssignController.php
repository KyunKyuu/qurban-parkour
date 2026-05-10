<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
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

    /**
     * Show the form for assigning vouchers.
     */
    public function create()
    {
        $pics = Pic::where('is_active', true)->with('communities')->get();
        $batches = VoucherBatch::latest()->get();
        $availableCount = $this->assignService->getAvailableCount();

        // Map komunitas per PIC untuk digunakan di view (JS)
        $communitiesByPic = $pics->mapWithKeys(fn ($pic) => [
            $pic->id => $pic->communities->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->values(),
        ]);

        return view('admin.vouchers.assign', compact('pics', 'batches', 'availableCount', 'communitiesByPic'));
    }

    /**
     * Assign vouchers to a PIC.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pic_id'       => 'required|exists:pics,id',
            'community_id' => 'nullable|exists:communities,id',
            'qty'          => 'required|integer|min:1',
            'batch_id'     => 'nullable|exists:voucher_batches,id',
        ]);

        try {
            $count = $this->assignService->assign(
                $validated['pic_id'],
                $validated['qty'],
                $validated['batch_id'] ?? null,
                $validated['community_id'] ?? null,
            );

            $pic = Pic::find($validated['pic_id']);
            $communityLabel = isset($validated['community_id'])
                ? ' (komunitas: ' . Community::find($validated['community_id'])?->name . ')'
                : ' (langsung / tanpa komunitas)';

            return redirect()
                ->route('admin.vouchers.assign')
                ->with('success', "Berhasil assign {$count} voucher ke {$pic->name}{$communityLabel}");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal assign voucher: ' . $e->getMessage());
        }
    }
}
