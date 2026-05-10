@extends('layouts.pic')

@section('title', 'Dashboard Komunitas')

@section('content')
@php
    $filterParams = request()->only(['date_from', 'date_to', 'category_type', 'certificate_status', 'search']);
@endphp
<div class="space-y-6">

    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-[2rem] bg-[#1a3628] px-7 py-8 text-[#f0ebe0] shadow-[0_24px_64px_rgba(26,54,40,0.28)]">
        <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full bg-white/[0.03]"></div>
        <div class="pointer-events-none absolute -left-8 bottom-0 h-40 w-40 rounded-full bg-white/[0.025]"></div>
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-[#a8c5b0]">PIC Komunitas</p>
                <h2 class="display-font mt-3 text-4xl leading-tight text-[#f0ebe0]">
                    {{ $community ? $community->name : $pic->name }}
                </h2>
                <p class="mt-2 text-sm leading-6 text-[#a8c5b0]">
                    @if($community)
                        Donatur &amp; sertifikat komunitas yang Anda kelola langsung.
                    @else
                        Anda belum ditugaskan ke komunitas manapun. Hubungi admin.
                    @endif
                </p>
            </div>
            @if($community)
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('pic.data.export-excel', $filterParams) }}"
                   class="rounded-full bg-[#e8a23e] px-5 py-3 text-sm font-semibold text-[#1a3628] transition hover:bg-[#d4913a]">
                    Download Excel
                </a>
                <a href="{{ route('pic.data.export', $filterParams) }}"
                   class="rounded-full border border-white/30 px-5 py-3 text-sm font-semibold text-[#f0ebe0] transition hover:bg-white/10">
                    Download CSV
                </a>
            </div>
            @endif
        </div>
    </section>

    @if(!$community)
        <div class="rounded-[1.8rem] border border-amber-200 bg-amber-50 px-6 py-5 text-amber-800">
            <p class="font-semibold">Komunitas belum diassign.</p>
            <p class="mt-1 text-sm">Hubungi admin untuk menghubungkan akun ini ke komunitas yang Anda kelola.</p>
        </div>
    @else

    {{-- KPI --}}
    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Total Kontribusi</p>
            <p class="mt-3 text-3xl font-extrabold text-[#1a3628]">{{ number_format($stats['total_claims']) }}</p>
            <p class="mt-1 text-xs text-stone-400">{{ $community->name }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Nominal Terkumpul</p>
            <p class="mt-3 text-2xl font-extrabold text-[#1a3628]">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Sertifikat Generated</p>
            <p class="mt-3 text-3xl font-extrabold text-[#1a3628]">{{ number_format($stats['certificates_generated']) }}</p>
            <p class="mt-1 text-xs text-stone-400">dari {{ number_format($stats['total_claims']) }} kontribusi</p>
        </div>
    </section>

    {{-- Export Voucher PDF --}}
    <section class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Brosur / Voucher PDF</p>
                <h3 class="mt-1.5 text-lg font-bold text-[#1a3628]">Unduh PDF voucher komunitas ini</h3>
                <p class="mt-1 text-sm text-stone-500">Satu file ZIP berisi satu PDF per voucher yang dimiliki komunitas Anda.</p>
            </div>
            <form method="GET" action="{{ route('pic.vouchers.export-pdf-komunitas') }}">
                <input type="hidden" name="community_id" value="{{ $community->id }}">
                <button type="submit"
                    class="rounded-full bg-[#1a3628] px-6 py-3 text-sm font-semibold text-[#e8a23e] transition hover:bg-[#0f2d1e] whitespace-nowrap">
                    Download ZIP Voucher
                </button>
            </form>
        </div>
    </section>

    {{-- Category Stats + Donor Table --}}
    <section class="grid gap-6 lg:grid-cols-[0.38fr_0.62fr]">

        {{-- Ringkasan Kategori --}}
        <div class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Ringkasan Kategori</p>
            <h3 class="mt-1.5 text-lg font-bold text-[#1a3628]">{{ $community->name }}</h3>

            <div class="mt-5 divide-y divide-[#f0ebe0]">
                @forelse($categoryStats as $category)
                    <div class="flex items-center justify-between py-4">
                        <div>
                            <p class="font-semibold text-[#1a3628]">{{ $category['label'] }}</p>
                            <p class="text-xs text-stone-400">{{ number_format($category['total_claims']) }} kontribusi</p>
                        </div>
                        <p class="font-bold text-[#1a3628]">Rp {{ number_format($category['total_amount'], 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="py-6 text-sm text-stone-400">Belum ada data kontribusi.</p>
                @endforelse
            </div>
        </div>

        {{-- Donor Table with per-row Certificate Download --}}
        <div class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Daftar Donatur</p>
                    <h3 class="mt-1.5 text-lg font-bold text-[#1a3628]">Poster apresiasi per donatur</h3>
                </div>
            </div>

            <form method="GET" class="mt-5 flex flex-wrap gap-3">
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <select name="category_type" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                    <option value="">Semua kategori</option>
                    @foreach($pricingOptions as $category)
                        <option value="{{ $category['key'] }}" {{ request('category_type') === $category['key'] ? 'selected' : '' }}>
                            {{ $category['label'] }}
                        </option>
                    @endforeach
                </select>
                <select name="certificate_status" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                    <option value="">Semua sertifikat</option>
                    <option value="generated" {{ request('certificate_status') === 'generated' ? 'selected' : '' }}>Sudah generated</option>
                    <option value="missing" {{ request('certificate_status') === 'missing' ? 'selected' : '' }}>Belum generated</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama, email…"
                    class="rounded-2xl border border-stone-300 px-4 py-3 text-sm flex-1 min-w-[180px]">
                <button type="submit"
                    class="rounded-full bg-[#1a3628] px-5 py-3 text-sm font-semibold text-[#e8a23e]">Filter</button>
                @if(request()->hasAny(['date_from', 'date_to', 'category_type', 'certificate_status', 'search']))
                    <a href="{{ route('pic.dashboard') }}"
                       class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">Reset</a>
                @endif
            </form>

            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-stone-400">
                        <tr>
                            <th class="pb-3 pr-4 font-semibold">Donatur</th>
                            <th class="pb-3 pr-4 font-semibold">Kategori</th>
                            <th class="pb-3 pr-4 font-semibold">Nominal</th>
                            <th class="pb-3 pr-4 font-semibold">Sertifikat</th>
                            <th class="pb-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0ebe0]">
                        @forelse($claims as $claim)
                            <tr class="align-top">
                                <td class="py-4 pr-4">
                                    <p class="font-semibold text-[#1a3628]">{{ $claim->name }}</p>
                                    <p class="text-xs text-stone-400">{{ $claim->email }}</p>
                                    @if($claim->instagram_username)
                                        <p class="text-xs text-stone-400">@{{ ltrim($claim->instagram_username, '@') }}</p>
                                    @endif
                                </td>
                                <td class="py-4 pr-4 text-stone-700">{{ $claim->display_category_label }}</td>
                                <td class="py-4 pr-4 font-semibold text-[#1a3628]">
                                    Rp {{ number_format($claim->contribution_amount ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="py-4 pr-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-semibold
                                        {{ $claim->certificate_generated_at ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-500' }}">
                                        {{ $claim->certificate_generated_at ? 'Generated' : 'Belum' }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    <a href="{{ route('pic.claims.certificate', $claim->id) }}"
                                       class="inline-flex items-center gap-1.5 rounded-full border border-stone-200 px-3 py-2 text-xs font-semibold text-stone-700 hover:border-[#1a3628] hover:text-[#1a3628] transition whitespace-nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Poster
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-stone-400">Belum ada kontribusi dengan filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($claims->hasPages())
                <div class="mt-5 border-t border-[#f0ebe0] pt-4">{{ $claims->links() }}</div>
            @endif
        </div>
    </section>

    @endif {{-- end if $community --}}
</div>
@endsection
