@extends('layouts.admin')

@section('title', 'Export Data')

@section('content')
<div class="space-y-6">

    {{-- KPI --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Total Kontribusi</p>
            <p class="mt-3 text-3xl font-extrabold text-[#1a3628]">{{ number_format($stats['total_claims']) }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Nominal Terkumpul</p>
            <p class="mt-3 text-2xl font-extrabold text-[#1a3628]">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Sertifikat Generated</p>
            <p class="mt-3 text-3xl font-extrabold text-[#1a3628]">{{ number_format($stats['total_certificates']) }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Total Kode Voucher</p>
            <p class="mt-3 text-3xl font-extrabold text-[#1a3628]">{{ number_format($stats['total_vouchers']) }}</p>
        </div>
    </section>

    {{-- Export Kontribusi --}}
    <section class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Export Kontribusi</p>
        <h2 class="mt-1.5 text-xl font-bold text-[#1a3628]">Unduh data peserta &amp; kontribusi kurban</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-stone-500">
            CSV memuat identitas peserta, kategori, nominal, channel (Voucher PIC / Direct Web), komunitas, PIC, status verifikasi, dan status sertifikat.
        </p>

        <form method="GET" action="{{ route('admin.exports.claims') }}" class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="rounded-2xl border border-stone-300 px-4 py-3 text-sm" placeholder="Dari tanggal">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="rounded-2xl border border-stone-300 px-4 py-3 text-sm" placeholder="Sampai tanggal">
            <select name="pic_id" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua PIC</option>
                @foreach($pics as $pic)
                    <option value="{{ $pic->id }}" {{ (string) request('pic_id') === (string) $pic->id ? 'selected' : '' }}>
                        {{ $pic->name }}
                    </option>
                @endforeach
            </select>
            <select name="community_id" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua Komunitas</option>
                @foreach($communities as $community)
                    <option value="{{ $community->id }}" {{ (string) request('community_id') === (string) $community->id ? 'selected' : '' }}>
                        {{ $community->name }}
                    </option>
                @endforeach
            </select>
            <select name="category_type" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category['key'] }}" {{ request('category_type') === $category['key'] ? 'selected' : '' }}>
                        {{ $category['label'] }}
                    </option>
                @endforeach
            </select>
            <select name="certificate_status" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua status sertifikat</option>
                <option value="generated" {{ request('certificate_status') === 'generated' ? 'selected' : '' }}>Sudah generated</option>
                <option value="missing" {{ request('certificate_status') === 'missing' ? 'selected' : '' }}>Belum generated</option>
            </select>
            <select name="source_channel" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua channel</option>
                <option value="voucher" {{ request('source_channel') === 'voucher' ? 'selected' : '' }}>Voucher PIC</option>
                <option value="direct" {{ request('source_channel') === 'direct' ? 'selected' : '' }}>Direct web</option>
            </select>
            <select name="verification_status" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua verifikasi</option>
                <option value="VERIFIED" {{ request('verification_status') === 'VERIFIED' ? 'selected' : '' }}>Terverifikasi</option>
                <option value="PENDING" {{ request('verification_status') === 'PENDING' ? 'selected' : '' }}>Menunggu</option>
                <option value="ANOMALY" {{ request('verification_status') === 'ANOMALY' ? 'selected' : '' }}>Anomali</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama, email, IG, token, kode…"
                class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">

            <div class="md:col-span-2 xl:col-span-3 flex flex-wrap gap-3 pt-1">
                <button type="submit"
                    class="rounded-full bg-[#1a3628] px-6 py-3 text-sm font-semibold text-[#e8a23e] transition hover:bg-[#0f2d1e]">
                    Unduh Kontribusi CSV
                </button>
                <a href="{{ route('admin.exports.index') }}"
                   class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">
                    Reset Filter
                </a>
            </div>
        </form>
    </section>

    {{-- Export Voucher --}}
    <section class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Export Voucher</p>
        <h2 class="mt-1.5 text-xl font-bold text-[#1a3628]">Unduh data kode voucher</h2>
        <p class="mt-2 text-sm leading-6 text-stone-500">
            CSV memuat semua kode voucher beserta komunitas, PIC, dan data peserta yang menggunakannya.
        </p>

        <form method="GET" action="{{ route('admin.exports.vouchers') }}" class="mt-5 flex flex-wrap gap-3">
            <select name="community_id" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua Komunitas</option>
                @foreach($communities as $community)
                    <option value="{{ $community->id }}">{{ $community->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua status</option>
                <option value="ASSIGNED">Assigned</option>
                <option value="CLAIMED">Claimed</option>
                <option value="GENERATED">Generated</option>
            </select>
            <button type="submit"
                class="rounded-full border border-[#1a3628] px-5 py-3 text-sm font-semibold text-[#1a3628] transition hover:bg-[#1a3628] hover:text-[#e8a23e]">
                Unduh Voucher CSV
            </button>
        </form>
    </section>

</div>
@endsection
