@extends('layouts.admin')

@section('title', 'Sales (PIC Kasie)')

@section('content')
<div class="space-y-6">

    {{-- KPI --}}
    <section class="grid gap-4 md:grid-cols-4">
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Total Sales</p>
            <p class="mt-3 text-3xl font-extrabold text-[#1a3628]">{{ number_format($totalSales) }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Voucher Langsung</p>
            <p class="mt-3 text-3xl font-extrabold text-[#1a3628]">{{ number_format($totalVouchers) }}</p>
            <p class="mt-1 text-xs text-stone-400">Tanpa komunitas</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Total Peserta Terdaftar</p>
            <p class="mt-3 text-3xl font-extrabold text-[#1a3628]">{{ number_format($totalRegistered) }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Total Nominal Terkumpul</p>
            <p class="mt-3 text-2xl font-extrabold text-[#1a3628]">Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
        </div>
    </section>

    {{-- Filter --}}
    <section class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode sales"
                class="rounded-2xl border border-stone-300 px-4 py-3 text-sm flex-1 min-w-[200px]">
            <button type="submit" class="rounded-full bg-[#1a3628] px-5 py-3 text-sm font-semibold text-[#e8a23e]">Filter</button>
            @if(request()->has('search') && request('search') !== '')
                <a href="{{ route('admin.sales.index') }}" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">Reset</a>
            @endif
        </form>
    </section>

    {{-- Table --}}
    <section class="overflow-hidden rounded-[1.8rem] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-stone-50 text-stone-500">
                    <tr>
                        <th class="px-6 py-4 font-semibold">PIC Kasie / Sales</th>
                        <th class="px-6 py-4 font-semibold">Voucher Langsung</th>
                        <th class="px-6 py-4 font-semibold">Terdaftar</th>
                        <th class="px-6 py-4 font-semibold">Nominal</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($pics as $pic)
                    <tr class="hover:bg-stone-50 transition">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-[#1a3628]">{{ $pic->name }}</p>
                            <p class="text-xs text-stone-400 font-mono mt-0.5">{{ $pic->code ?? '-' }}</p>
                        </td>
                        <td class="px-6 py-4 font-semibold text-stone-900">{{ number_format($pic->voucher_count) }}</td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-stone-900">{{ number_format($pic->registered_count) }}</span>
                            @if($pic->voucher_count > 0)
                            <span class="text-xs text-stone-400 ml-1">/ {{ $pic->voucher_count }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-semibold text-[#1a3628]">Rp {{ number_format($pic->total_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-semibold {{ $pic->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-500' }}">
                                {{ $pic->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.sales.show', $pic->id) }}"
                               class="rounded-full bg-[#1a3628] px-4 py-2 text-xs font-semibold text-[#e8a23e] transition hover:bg-[#0f2d1e]">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-stone-400">Belum ada PIC Kasie yang ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>
@endsection
