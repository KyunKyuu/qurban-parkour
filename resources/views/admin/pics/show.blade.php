@extends('layouts.admin')

@section('title', 'Detail PJ')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.pics.index') }}" class="text-emerald-600 hover:text-emerald-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-bold text-emerald-950">{{ $pic->name }}</h2>
                    @if($pic->isKomunitas())
                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-sky-100 text-sky-800">PIC Komunitas</span>
                    @else
                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">PIC Kasie</span>
                    @endif
                </div>
                <p class="text-emerald-600 mt-1">Informasi lengkap dan daftar voucher yang di-assign</p>
            </div>
        </div>
        <a href="{{ route('admin.pics.edit', $pic) }}"
            class="rounded-full bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 transition">
            Edit PJ
        </a>
    </div>

    {{-- PIC Info Card --}}
    <div class="rounded-[1.6rem] bg-white border border-emerald-100 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs uppercase tracking-[0.22em] text-emerald-500">Nama</p>
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

        {{-- Relasi info --}}
        <div class="mt-5 pt-5 border-t border-emerald-100">
            @if($pic->isKomunitas())
                <p class="text-xs uppercase tracking-[0.22em] text-emerald-500 mb-3">Komunitas yang Dikelola</p>
                @if($pic->communityAsPicKomunitas)
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 border border-sky-200 px-3 py-1.5 text-sm font-medium text-sky-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            {{ $pic->communityAsPicKomunitas->name }}
                            <span class="text-sky-400 font-mono text-xs">({{ $pic->communityAsPicKomunitas->code }})</span>
                        </span>
                        <a href="{{ route('admin.communities.show', $pic->communityAsPicKomunitas->id) }}"
                            class="text-xs text-emerald-600 hover:text-emerald-900 underline">Lihat Komunitas →</a>
                        @if($pic->communityAsPicKomunitas->pic)
                            <span class="text-xs text-stone-400">— Kasie: <strong>{{ $pic->communityAsPicKomunitas->pic->name }}</strong></span>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-stone-400 italic">Belum ada komunitas yang ditugaskan.</p>
                @endif
            @else
                <p class="text-xs uppercase tracking-[0.22em] text-emerald-500 mb-3">PIC Komunitas Bawahan</p>
                @php $subs = $pic->communities->filter(fn($c) => $c->picKomunitas !== null) @endphp
                @if($subs->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($subs as $community)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 border border-amber-200 px-3 py-1.5 text-sm font-medium text-amber-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                {{ $community->picKomunitas->name }}
                                <span class="text-amber-400 text-xs">— {{ $community->name }}</span>
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-stone-400 italic">Belum ada PIC Komunitas bawahan.</p>
                @endif
            @endif
        </div>
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

    {{-- Voucher Table --}}
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
                            <td class="px-6 py-4 font-mono text-sm font-semibold text-emerald-900">{{ $voucher->code }}</td>
                            <td class="px-6 py-4">
                                @if($voucher->community)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">
                                        {{ $voucher->community->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-emerald-400 italic">Langsung</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-emerald-700">{{ $voucher->batch->name ?? '-' }}</td>
                            <td class="px-6 py-4">
                                @if($voucher->status === 'ASSIGNED')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-700">Menunggu</span>
                                @elseif($voucher->status === 'CLAIMED')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Terdaftar</span>
                                @else
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-stone-100 text-stone-600">{{ $voucher->status }}</span>
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
                            <td class="px-6 py-4 text-emerald-700">{{ $voucher->claim?->category_label ?? '-' }}</td>
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
