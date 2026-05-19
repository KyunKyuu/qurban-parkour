<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $query = Pic::where('pic_type', 'kasie');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%");
            });
        }

        $pics = $query->orderBy('name')->get();

        $picIds = $pics->pluck('id');

        // Direct voucher counts (community_id IS NULL)
        $voucherStats = DB::table('initial_vouchers')
            ->whereIn('assigned_pic_id', $picIds)
            ->whereNull('community_id')
            ->groupBy('assigned_pic_id')
            ->selectRaw('assigned_pic_id, COUNT(*) as voucher_count')
            ->get()
            ->keyBy('assigned_pic_id');

        // Claim stats for direct vouchers
        $claimStats = DB::table('initial_vouchers')
            ->join('claims', 'claims.initial_voucher_id', '=', 'initial_vouchers.id')
            ->whereNull('initial_vouchers.community_id')
            ->whereIn('initial_vouchers.assigned_pic_id', $picIds)
            ->whereNull('claims.deleted_at')
            ->groupBy('initial_vouchers.assigned_pic_id')
            ->selectRaw('
                initial_vouchers.assigned_pic_id,
                COUNT(*) as registered_count,
                COALESCE(SUM(COALESCE(claims.contribution_amount, 0)), 0) as total_amount
            ')
            ->get()
            ->keyBy('assigned_pic_id');

        $pics->each(function (Pic $pic) use ($voucherStats, $claimStats) {
            $v = $voucherStats[$pic->id] ?? null;
            $c = $claimStats[$pic->id] ?? null;
            $pic->voucher_count     = $v ? (int)   $v->voucher_count    : 0;
            $pic->registered_count  = $c ? (int)   $c->registered_count : 0;
            $pic->total_amount      = $c ? (float) $c->total_amount     : 0.0;
        });

        $totalSales      = $pics->count();
        $totalVouchers   = $pics->sum('voucher_count');
        $totalRegistered = $pics->sum('registered_count');
        $totalAmount     = $pics->sum('total_amount');

        return view('admin.sales.index', compact(
            'pics',
            'totalSales',
            'totalVouchers',
            'totalRegistered',
            'totalAmount',
        ));
    }

    public function show(Request $request, int $id)
    {
        $pic = Pic::where('pic_type', 'kasie')->findOrFail($id);

        $claimQuery = DB::table('claims')
            ->join('initial_vouchers', 'initial_vouchers.id', '=', 'claims.initial_voucher_id')
            ->where('initial_vouchers.assigned_pic_id', $id)
            ->whereNull('initial_vouchers.community_id')
            ->whereNull('claims.deleted_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $claimQuery->where(function ($q) use ($s) {
                $q->where('claims.name', 'like', "%{$s}%")
                  ->orWhere('claims.email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category_type')) {
            $claimQuery->where('claims.category_type', $request->category_type);
        }

        $stats = [
            'voucher_count'    => DB::table('initial_vouchers')
                ->where('assigned_pic_id', $id)
                ->whereNull('community_id')
                ->count(),
            'registered_count' => (int)   (clone $claimQuery)->count(),
            'total_amount'     => (float) (clone $claimQuery)->sum('claims.contribution_amount'),
            'certificates'     => (int)   (clone $claimQuery)->whereNotNull('claims.certificate_generated_at')->count(),
        ];

        $claims = $claimQuery
            ->select(
                'claims.id',
                'claims.name',
                'claims.email',
                'claims.phone',
                'claims.category_type',
                'claims.category_label',
                'claims.contribution_amount',
                'claims.payment_method',
                'claims.certificate_generated_at',
                'claims.created_at',
                'initial_vouchers.code as voucher_code',
            )
            ->orderByDesc('claims.created_at')
            ->paginate(20)
            ->withQueryString();

        $categoryOptions = DB::table('claims')
            ->join('initial_vouchers', 'initial_vouchers.id', '=', 'claims.initial_voucher_id')
            ->where('initial_vouchers.assigned_pic_id', $id)
            ->whereNull('initial_vouchers.community_id')
            ->whereNull('claims.deleted_at')
            ->select('claims.category_type', 'claims.category_label')
            ->distinct()
            ->get();

        return view('admin.sales.show', compact(
            'pic',
            'stats',
            'claims',
            'categoryOptions',
        ));
    }
}
