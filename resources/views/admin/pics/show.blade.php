@extends('layouts.admin')

@section('title', 'Detail PJ')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.pics.index') }}" class="text-emerald-600 hover:text-emerald-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h2 class="text-2xl font-bold text-emerald-950">Detail PJ</h2>
            </div>
            <p class="text-emerald-600 mt-1">Informasi lengkap dan daftar voucher yang di-assign</p>
        </div>
        <a href="{{ route('admin.pics.edit', $pic) }}" class="bg-emerald-700 text-white px-4 py-2 rounded-lg hover:bg-emerald-800 text-sm font-medium">
            Edit PJ
        </a>
    </div>

    {{-- PJ Info Card --}}
    <div class="rounded-[1.6rem] bg-white border border-emerald-100 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.22em] text-emerald-500">Nama PJ</p>
                <p class="mt-2 text-lg font-bold text-emerald-950">{{ $pic->name }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.22em] text-emerald-500">Kode</p>
                <p class="mt-2 text-lg font-mono font-semibold text-emerald-950">{{ $pic->code ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.22em] text-emerald-500">Email</p>
                <p class="mt-2 text-sm text-emerald-800">{{ $pic->email ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-[0.22em] text-emerald-500">Status</p>
                <div class="mt-2">
                    @if($pic->is_active)
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                    @else
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-stone-100 text-stone-600">Nonaktif</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Komunitas milik PJ ini --}}
        @if($pic->communities && $pic->communities->count() > 0)
        <div class="mt-5 pt-5 border-t border-emerald-100">
            <p class="text-xs uppercase tracking-[0.22em] text-emerald-500 mb-3">Komunitas</p>
            <div class="flex flex-wrap gap-2">
                @foreach($pic->communities as $community)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-sm font-medium text-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ $community->name }}
                        <span class="text-emerald-400 font-mono text-xs">({{ $community->code }})</span>
                    </span>
                @endforeach
            </div>
        </div>
        @else
        <div class="mt-5 pt-5 border-t border-emerald-100">
            <p class="text-xs uppercase tracking-[0.22em] text-emerald-500 mb-1">Komunitas</p>
            <p class="text-sm text-emerald-400 italic">PJ Default — voucher langsung tanpa komunitas</p>
        </div>
        @endif
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-[1.6rem] bg-white border border-emerald-100 p-5 shadow-sm">
            <p class="text-sm text-emerald-600">Total Voucher</p>
            <p class="mt-3 text-3xl font-extrabold text-emerald-950">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-[1.6rem] bg-white border border-orange-100 p-5 shadow-sm">
            <p class="text-sm text-orange-600">Menunggu Pendaftar</p>
            <p class="mt-3 text-3xl font-extrabold text-orange-600">{{ $stats['assigned'] }}</p>
            <p class="mt-1 text-xs text-orange-400">Voucher sudah di-assign, belum dipakai</p>
        </div>
        <div class="rounded-[1.6rem] bg-white border border-emerald-100 p-5 shadow-sm">
            <p class="text-sm text-emerald-600">Peserta Terdaftar</p>
            <p class="mt-3 text-3xl font-extrabold text-emerald-700">{{ $stats['claimed'] }}</p>
            <p class="mt-1 text-xs text-emerald-400">Voucher sudah dipakai untuk daftar</p>
        </div>
    </div>

    {{-- Tabel Voucher --}}
    <div class="rounded-[1.8rem] bg-white border border-emerald-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-emerald-100">
            <h3 class="text-lg font-semibold text-emerald-950">Daftar Voucher</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-100 text-sm">
                <thead class="bg-emerald-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Kode Voucher</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Komunitas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Nama Peserta</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Waktu Daftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                    @forelse($pic->initialVouchers as $voucher)
                        <tr class="hover:bg-emerald-50/50">
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm font-semibold text-emerald-900">{{ $voucher->code }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($voucher->community)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                        {{ $voucher->community->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-emerald-400 italic">Langsung (default)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-emerald-700">
                                {{ $voucher->batch->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($voucher->status === 'ASSIGNED')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-700">
                                        Menunggu Pendaftar
                                    </span>
                                @elseif($voucher->status === 'CLAIMED')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                        Peserta Terdaftar
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-stone-100 text-stone-600">
                                        {{ $voucher->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($voucher->claim)
                                    <p class="font-semibold text-emerald-950">{{ $voucher->claim->name }}</p>
                                    <p class="text-xs text-emerald-500">{{ $voucher->claim->email }}</p>
                                @else
                                    <span class="text-emerald-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($voucher->claim?->category_label)
                                    <span class="text-sm text-emerald-700">{{ $voucher->claim->category_label }}</span>
                                @else
                                    <span class="text-emerald-300">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-emerald-600 text-xs">
                                {{ $voucher->claimed_at?->format('d M Y, H:i') ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-emerald-400">
                                Belum ada voucher yang di-assign ke PJ ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
