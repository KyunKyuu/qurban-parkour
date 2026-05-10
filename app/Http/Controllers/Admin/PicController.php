<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Community;
use App\Models\Pic;
use App\Models\User;
use Illuminate\Http\Request;

class PicController extends Controller
{
    public function index()
    {
        $pics = Pic::withCount('initialVouchers')
            ->with(['communityAsPicKomunitas', 'communities.picKomunitas'])
            ->latest()
            ->paginate(15);

        return view('admin.pics.index', compact('pics'));
    }

    public function create()
    {
        $communities = $this->availableCommunities();
        $picKomunitas = $this->allPicKomunitas();

        return view('admin.pics.create', compact('communities', 'picKomunitas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => 'nullable|string|max:20|unique:pics,code',
            'email'            => 'required|email|max:255|unique:pics,email|unique:users,email',
            'password'         => 'required|string|min:8|confirmed',
            'is_active'        => 'boolean',
            'pic_type'         => 'required|in:kasie,komunitas',
            'community_id'     => 'nullable|exists:communities,id',
            'subordinate_ids'  => 'nullable|array',
            'subordinate_ids.*'=> 'exists:pics,id',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $passwordHash = bcrypt($validated['password']);

        $pic = Pic::create([
            'name'     => $validated['name'],
            'code'     => $validated['code'] ?? null,
            'email'    => $validated['email'],
            'password' => $passwordHash,
            'is_active' => $validated['is_active'],
            'pic_type' => $validated['pic_type'],
        ]);

        User::create([
            'name'     => $pic->name,
            'email'    => $pic->email,
            'password' => $passwordHash,
            'role'     => 'PIC',
            'pic_id'   => $pic->id,
        ]);

        if ($validated['pic_type'] === 'komunitas' && !empty($validated['community_id'])) {
            Community::where('id', $validated['community_id'])
                ->update(['pic_komunitas_id' => $pic->id]);
        }

        if ($validated['pic_type'] === 'kasie') {
            $this->assignSubordinates($pic, $request->input('subordinate_ids', []));
        }

        return redirect()->route('admin.pics.index')->with('success', 'PIC berhasil ditambahkan');
    }

    public function show(Pic $pic)
    {
        $pic->load([
            'communityAsPicKomunitas',
            'communities.picKomunitas',
            'initialVouchers' => function ($query) {
                $query->with(['claim', 'batch', 'community'])
                    ->orderByRaw("CASE WHEN status = 'ASSIGNED' THEN 1 WHEN status = 'CLAIMED' THEN 2 ELSE 3 END")
                    ->orderBy('code');
            },
        ]);

        $stats = [
            'total'    => $pic->initialVouchers->count(),
            'assigned' => $pic->initialVouchers->where('status', 'ASSIGNED')->count(),
            'claimed'  => $pic->initialVouchers->where('status', 'CLAIMED')->count(),
        ];

        return view('admin.pics.show', compact('pic', 'stats'));
    }

    public function edit(Pic $pic)
    {
        $pic->load(['communityAsPicKomunitas', 'communities.picKomunitas']);

        $communities = $this->availableCommunities($pic);
        $picKomunitas = $this->allPicKomunitas($pic);

        return view('admin.pics.edit', compact('pic', 'communities', 'picKomunitas'));
    }

    public function update(Request $request, Pic $pic)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:100',
            'code'             => 'nullable|string|max:20|unique:pics,code,' . $pic->id,
            'email'            => 'required|email|max:255|unique:pics,email,' . $pic->id,
            'password'         => 'nullable|string|min:8|confirmed',
            'is_active'        => 'boolean',
            'pic_type'         => 'required|in:kasie,komunitas',
            'community_id'     => 'nullable|exists:communities,id',
            'subordinate_ids'  => 'nullable|array',
            'subordinate_ids.*'=> 'exists:pics,id',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $data = [
            'name'     => $validated['name'],
            'code'     => $validated['code'] ?? null,
            'email'    => $validated['email'],
            'is_active' => $validated['is_active'],
            'pic_type' => $validated['pic_type'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        $pic->update($data);

        $user = User::where('pic_id', $pic->id)->first();
        if ($user) {
            $user->email = $validated['email'];
            if (isset($data['password'])) $user->password = $data['password'];
            $user->save();
        } elseif (isset($data['password'])) {
            User::create([
                'name'     => $pic->name,
                'email'    => $pic->email,
                'password' => $data['password'],
                'role'     => 'PIC',
                'pic_id'   => $pic->id,
            ]);
        }

        // Clear old community assignment (komunitas type)
        Community::where('pic_komunitas_id', $pic->id)->update(['pic_komunitas_id' => null]);

        if ($validated['pic_type'] === 'komunitas' && !empty($validated['community_id'])) {
            Community::where('id', $validated['community_id'])
                ->update(['pic_komunitas_id' => $pic->id]);
        }

        // Clear old kasie assignments then re-assign
        Community::where('pic_id', $pic->id)->update(['pic_id' => null]);

        if ($validated['pic_type'] === 'kasie') {
            $this->assignSubordinates($pic, $request->input('subordinate_ids', []));
        }

        return redirect()->route('admin.pics.index')->with('success', 'PIC berhasil diupdate');
    }

    public function destroy(Pic $pic)
    {
        if ($pic->initialVouchers()->count() > 0) {
            return back()->with('error', 'PIC tidak dapat dihapus karena masih memiliki voucher yang di-assign');
        }

        Community::where('pic_komunitas_id', $pic->id)->update(['pic_komunitas_id' => null]);
        Community::where('pic_id', $pic->id)->update(['pic_id' => null]);

        $pic->delete();
        User::where('pic_id', $pic->id)->delete();

        return redirect()->route('admin.pics.index')->with('success', 'PIC berhasil dihapus');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Assign Kasie to communities by selecting PIC Komunitas subordinates.
     * Finds each PIC Komunitas's community and sets pic_id to this Kasie.
     */
    protected function assignSubordinates(Pic $kasie, array $subordinateIds): void
    {
        if (empty($subordinateIds)) return;

        // Get communities managed by the selected PIC Komunitas
        $communityIds = Community::whereIn('pic_komunitas_id', $subordinateIds)
            ->pluck('id')
            ->toArray();

        if (!empty($communityIds)) {
            Community::whereIn('id', $communityIds)->update(['pic_id' => $kasie->id]);
        }
    }

    /** Communities available for PIC Komunitas assignment */
    protected function availableCommunities(?Pic $exclude = null): \Illuminate\Support\Collection
    {
        return Community::where('is_active', true)
            ->with('pic')
            ->where(function ($q) use ($exclude) {
                $q->whereNull('pic_komunitas_id');
                if ($exclude && $exclude->isKomunitas()) {
                    $q->orWhere('pic_komunitas_id', $exclude->id);
                }
            })
            ->orderBy('name')
            ->get();
    }

    /** All PIC Komunitas for Kasie subordinate selection */
    protected function allPicKomunitas(?Pic $exclude = null): \Illuminate\Support\Collection
    {
        return Pic::where('pic_type', 'komunitas')
            ->with(['communityAsPicKomunitas.pic'])
            ->when($exclude, fn ($q) => $q->where('id', '!=', $exclude->id))
            ->orderBy('name')
            ->get();
    }
}
