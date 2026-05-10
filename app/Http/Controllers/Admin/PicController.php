<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pic;
use App\Models\User;
use Illuminate\Http\Request;

class PicController extends Controller
{
    /**
     * Display a listing of PICs.
     */
    public function index()
    {
        $pics = Pic::withCount('initialVouchers')
            ->latest()
            ->paginate(15);

        return view('admin.pics.index', compact('pics'));
    }

    /**
     * Show the form for creating a new PIC.
     */
    public function create()
    {
        return view('admin.pics.create');
    }

    /**
     * Store a newly created PIC.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20|unique:pics,code',
            'email' => 'required|email|max:255|unique:pics,email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $passwordHash = bcrypt($validated['password']);

        $pic = Pic::create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'email' => $validated['email'],
            'password' => $passwordHash,
            'is_active' => $validated['is_active'],
        ]);

        User::create([
            'name' => $pic->name,
            'email' => $pic->email,
            'password' => $passwordHash,
            'role' => 'PIC',
            'pic_id' => $pic->id,
        ]);

        return redirect()
            ->route('admin.pics.index')
            ->with('success', 'PIC berhasil ditambahkan');
    }

    /**
     * Display the specified PIC with vouchers.
     */
    public function show(Pic $pic)
    {
        // Load vouchers with claim and batch relationships
        $pic->load(['initialVouchers' => function ($query) {
            $query->with(['claim', 'batch', 'community'])
                ->orderByRaw("CASE WHEN status = 'ASSIGNED' THEN 1 WHEN status = 'CLAIMED' THEN 2 ELSE 3 END")
                ->orderBy('code');
        }]);

        $claimedVouchers = $pic->initialVouchers->where('status', 'CLAIMED');
        $assignedVouchers = $pic->initialVouchers->where('status', 'ASSIGNED');

        // Calculate statistics
        $stats = [
            'total'    => $pic->initialVouchers->count(),
            'assigned' => $assignedVouchers->count(),
            'claimed'  => $claimedVouchers->count(),
        ];

        return view('admin.pics.show', compact('pic', 'stats'));
    }

    /**
     * Show the form for editing the specified PIC.
     */
    public function edit(Pic $pic)
    {
        return view('admin.pics.edit', compact('pic'));
    }

    /**
     * Update the specified PIC.
     */
    public function update(Request $request, Pic $pic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20|unique:pics,code,' . $pic->id,
            'email' => 'required|email|max:255|unique:pics,email,' . $pic->id,
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $pic->update($validated);

        // Update associated user email and password
        $user = User::where('pic_id', $pic->id)->first();
        if ($user) {
            $user->email = $validated['email'];
            if (isset($validated['password'])) {
                $user->password = $validated['password'];
            }
            $user->save();
        } else {
            // Create user if not exists (fallback for existing PICs)
            if (isset($validated['password'])) {
                User::create([
                    'name' => $pic->name,
                    'email' => $pic->email,
                    'password' => $validated['password'],
                    'role' => 'PIC',
                    'pic_id' => $pic->id,
                ]);
            }
        }

        return redirect()
            ->route('admin.pics.index')
            ->with('success', 'PIC berhasil diupdate');
    }

    /**
     * Remove the specified PIC.
     */
    public function destroy(Pic $pic)
    {
        // Check if PIC has assigned vouchers
        if ($pic->initialVouchers()->count() > 0) {
            return back()->with('error', 'PIC tidak dapat dihapus karena masih memiliki voucher yang di-assign');
        }

        $pic->delete();

        // Delete associated user
        User::where('pic_id', $pic->id)->delete();

        return redirect()
            ->route('admin.pics.index')
            ->with('success', 'PIC berhasil dihapus');
    }
}
