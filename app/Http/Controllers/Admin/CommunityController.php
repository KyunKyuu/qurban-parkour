<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Pic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $query = Community::with('pic')->withCount('initialVouchers as voucher_count');

        if ($request->filled('pic_id')) {
            $query->where('pic_id', $request->pic_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%");
            });
        }

        $communities = $query->orderBy('name')->get();

        // Fetch registered count + total amount per community via raw join
        $stats = DB::table('initial_vouchers')
            ->join('claims', 'claims.initial_voucher_id', '=', 'initial_vouchers.id')
            ->whereNull('claims.deleted_at')
            ->whereNotNull('initial_vouchers.community_id')
            ->groupBy('initial_vouchers.community_id')
            ->selectRaw('
                initial_vouchers.community_id,
                COUNT(*) as registered_count,
                COALESCE(SUM(COALESCE(claims.contribution_amount, 0)), 0) as total_amount
            ')
            ->get()
            ->keyBy('community_id');

        $communities->each(function (Community $community) use ($stats) {
            $s = $stats[$community->id] ?? null;
            $community->registered_count = $s ? (int) $s->registered_count : 0;
            $community->total_amount     = $s ? (float) $s->total_amount   : 0.0;
        });

        $totalCommunities = $communities->count();
        $totalRegistered  = $communities->sum('registered_count');
        $totalAmount      = $communities->sum('total_amount');

        $pics = Pic::orderBy('name')->get();

        return view('admin.communities.index', compact(
            'communities',
            'totalCommunities',
            'totalRegistered',
            'totalAmount',
            'pics',
        ));
    }

    public function show(Request $request, int $id)
    {
        $community = Community::with('pic')->findOrFail($id);

        $claimQuery = DB::table('claims')
            ->join('initial_vouchers', 'initial_vouchers.id', '=', 'claims.initial_voucher_id')
            ->where('initial_vouchers.community_id', $id)
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
            'voucher_count'    => $community->initialVouchers()->count(),
            'registered_count' => (int) (clone $claimQuery)->count(),
            'total_amount'     => (float) (clone $claimQuery)->sum('claims.contribution_amount'),
            'certificates'     => (int) (clone $claimQuery)->whereNotNull('claims.certificate_generated_at')->count(),
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
            ->where('initial_vouchers.community_id', $id)
            ->whereNull('claims.deleted_at')
            ->select('claims.category_type', 'claims.category_label')
            ->distinct()
            ->get();

        return view('admin.communities.show', compact(
            'community',
            'stats',
            'claims',
            'categoryOptions',
        ));
    }
}
