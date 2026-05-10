@extends('layouts.admin')

@section('title', $community->name)

@section('content')
<div class="space-y-6">

    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-[2rem] bg-[#1a3628] px-7 py-8 text-[#f0ebe0] shadow-[0_24px_64px_rgba(26,54,40,0.28)]">
        <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full bg-white/[0.03]"></div>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-[#a8c5b0]">
                    Komunitas · {{ $community->pic?->name ?? 'Tanpa PIC' }}
                </p>
                <h2 class="display-font mt-3 text-4xl leading-tight text-[#f0ebe0]">{{ $community->name }}</h2>
                <p class="mt-2 font-mono text-sm text-[#a8c5b0]">{{ $community->code }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="rounded-full border border-white/15 px-4 py-2 text-sm font-semibold {{ $community->is_active ? 'text-emerald-300' : 'text-stone-400' }}">
                    {{ $community->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
                <a href="{{ route('admin.communities.index') }}" class="rounded-full border border-white/15 px-5 py-3 text-sm font-semibold text-[#f0ebe0]">
                    ← Kembali
                </a>
            </div>
        </div>
    </section>

    {{-- KPI --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Voucher Diterbitkan</p>
            <p class="mt-3 text-3xl font-extrabold text-[#1a3628]">{{ number_format($stats['voucher_count']) }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Peserta Terdaftar</p>
            <p class="mt-3 text-3xl font-extrabold text-[#1a3628]">{{ number_format($stats['registered_count']) }}</p>
            @if($stats['voucher_count'] > 0)
            <p class="mt-1 text-xs text-stone-400">{{ round($stats['registered_count'] / $stats['voucher_count'] * 100) }}% dari voucher</p>
            @endif
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Total Nominal</p>
            <p class="mt-3 text-2xl font-extrabold text-[#1a3628]">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Sertifikat Generated</p>
            <p class="mt-3 text-3xl font-extrabold text-[#1a3628]">{{ number_format($stats['certificates']) }}</p>
        </div>
    </section>

    {{-- Participant list --}}
    <section class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Peserta Komunitas</p>
                <h3 class="mt-1.5 text-lg font-bold text-[#1a3628]">Daftar kontribusi dari komunitas ini</h3>
            </div>
        </div>

        <form method="GET" class="mt-5 flex flex-wrap gap-3">
            <select name="category_type" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua kategori</option>
                @foreach($categoryOptions as $opt)
                    <option value="{{ $opt->category_type }}" {{ request('category_type') === $opt->category_type ? 'selected' : '' }}>
                        {{ $opt->category_label ?: $opt->category_type }}
                    </option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email"
                class="rounded-2xl border border-stone-300 px-4 py-3 text-sm flex-1 min-w-[180px]">
            <button type="submit" class="rounded-full bg-[#1a3628] px-5 py-3 text-sm font-semibold text-[#e8a23e]">Filter</button>
            @if(request()->hasAny(['category_type', 'search']))
                <a href="{{ route('admin.communities.show', $community->id) }}" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">Reset</a>
            @endif
        </form>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-stone-400">
                    <tr>
                        <th class="pb-3 pr-5 font-semibold">Peserta</th>
                        <th class="pb-3 pr-5 font-semibold">Kategori</th>
                        <th class="pb-3 pr-5 font-semibold">Nominal</th>
                        <th class="pb-3 pr-5 font-semibold">Voucher</th>
                        <th class="pb-3 pr-5 font-semibold">Sertifikat</th>
                        <th class="pb-3 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0ebe0]">
                    @forelse($claims as $claim)
                    <tr class="align-top">
                        <td class="py-4 pr-5">
                            <p class="font-semibold text-[#1a3628]">{{ $claim->name }}</p>
                            <p class="text-xs text-stone-400">{{ $claim->email }}</p>
                        </td>
                        <td class="py-4 pr-5 text-stone-700">{{ $claim->category_label ?: $claim->category_type }}</td>
                        <td class="py-4 pr-5 font-semibold text-[#1a3628]">Rp {{ number_format($claim->contribution_amount ?? 0, 0, ',', '.') }}</td>
                        <td class="py-4 pr-5 font-mono text-xs text-stone-500">{{ $claim->voucher_code }}</td>
                        <td class="py-4 pr-5">
                            <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-semibold {{ $claim->certificate_generated_at ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-500' }}">
                                {{ $claim->certificate_generated_at ? 'Generated' : 'Belum' }}
                            </span>
                        </td>
                        <td class="py-4">
                            <a href="{{ route('admin.claims.show', $claim->id) }}"
                               class="rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 hover:border-[#1a3628] hover:text-[#1a3628] transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-stone-400">Belum ada peserta dari komunitas ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($claims->hasPages())
        <div class="mt-5 border-t border-[#f0ebe0] pt-4">{{ $claims->links() }}</div>
        @endif
    </section>

</div>
@endsection
