@extends('layouts.admin')

@section('title', 'Transaksi')

@section('content')
<div class="space-y-6">

    <div>
        <h2 class="text-2xl font-bold text-stone-950">Transaksi</h2>
        <p class="mt-1 text-sm text-stone-500">Semua transaksi kontribusi. Delete akan mereset voucher ke status ASSIGNED.</p>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter --}}
    <section class="rounded-[1.8rem] bg-white p-6 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <select name="category_type" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat['key'] }}" {{ request('category_type') === $cat['key'] ? 'selected' : '' }}>
                        {{ $cat['label'] }}
                    </option>
                @endforeach
            </select>

            <select name="verification_status" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua status</option>
                <option value="verified"   {{ request('verification_status') === 'verified'   ? 'selected' : '' }}>Verified</option>
                <option value="unverified" {{ request('verification_status') === 'unverified' ? 'selected' : '' }}>Belum verified</option>
            </select>

            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama, email, kode voucher…"
                class="rounded-2xl border border-stone-300 px-4 py-3 text-sm min-w-[220px] flex-1">

            <button type="submit"
                class="rounded-full bg-emerald-800 px-5 py-3 text-sm font-semibold text-amber-100">
                Filter
            </button>

            @if(request()->hasAny(['category_type', 'verification_status', 'search']))
                <a href="{{ route('admin.transactions.index') }}"
                   class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">
                    Reset
                </a>
            @endif
        </form>
    </section>

    {{-- Table --}}
    <section class="overflow-hidden rounded-[1.8rem] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-stone-100 text-stone-600">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Peserta</th>
                        <th class="px-5 py-4 font-semibold">Kategori</th>
                        <th class="px-5 py-4 font-semibold">Komunitas</th>
                        <th class="px-5 py-4 font-semibold">Nominal</th>
                        <th class="px-5 py-4 font-semibold">Status</th>
                        <th class="px-5 py-4 font-semibold">Tanggal</th>
                        <th class="px-5 py-4 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($claims as $claim)
                        <tr class="align-top hover:bg-stone-50">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-stone-900">{{ $claim->name }}</p>
                                <p class="text-xs text-stone-400">{{ $claim->email }}</p>
                            </td>
                            <td class="px-5 py-4 text-stone-700">{{ $claim->display_category_label }}</td>
                            <td class="px-5 py-4 text-stone-600 text-xs">
                                {{ $claim->initialVoucher?->community?->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 font-semibold text-stone-900">
                                Rp {{ number_format($claim->total_donation_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($claim->verified_at)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-700">Verified</span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold text-amber-700">Belum</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs text-stone-500">
                                {{ $claim->created_at->format('d M Y') }}<br>
                                <span class="text-stone-400">{{ $claim->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <form method="POST"
                                    action="{{ route('admin.claims.destroy', $claim->id) }}"
                                    onsubmit="return confirm('Hapus transaksi {{ addslashes($claim->name) }}? Voucher akan direset ke ASSIGNED.')">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect" value="admin.transactions.index">
                                    <button type="submit"
                                        class="rounded-full border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-stone-400">
                                Tidak ada transaksi yang cocok dengan filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($claims->hasPages())
            <div class="border-t border-stone-200 px-5 py-4">
                {{ $claims->links() }}
            </div>
        @endif
    </section>

</div>
@endsection
