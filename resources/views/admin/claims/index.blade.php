@extends('layouts.admin')

@section('title', 'Kontribusi & Sertifikat')

@section('content')
<div class="space-y-6">
    <div class="rounded-[1.3rem] border border-stone-200 bg-stone-50 px-5 py-4 text-sm text-stone-600">
        <span class="font-semibold text-stone-900">Scope ringkasan:</span> {{ $statsScopeLabel }}
    </div>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-[1.5rem] bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">Total Kontribusi</p>
            <p class="mt-2 text-2xl font-bold text-stone-950">{{ number_format($stats['total_claims']) }}</p>
        </div>
        <div class="rounded-[1.5rem] bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">Total Nominal</p>
            <p class="mt-2 text-2xl font-bold text-stone-950">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-[1.5rem] bg-white p-5 shadow-sm">
            <p class="text-sm text-stone-500">Sertifikat Generated</p>
            <p class="mt-2 text-2xl font-bold text-stone-950">{{ number_format($stats['certificates_generated']) }}</p>
        </div>
    </section>

    <section class="rounded-[1.8rem] bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.24em] text-stone-500">Filter Data</p>
                <h2 class="mt-1 text-2xl font-bold text-stone-950">Daftar kontribusi peserta</h2>
                <p class="mt-2 text-sm text-stone-500">Filter di bawah juga memengaruhi hasil export CSV.</p>
            </div>
            <a href="{{ route('admin.exports.claims', request()->only(['date_from', 'date_to', 'pic_id', 'category_type', 'certificate_status', 'source_channel', 'search'])) }}" class="inline-flex items-center justify-center rounded-full bg-[#1a3628] px-5 py-3 text-sm font-semibold text-[#e8a23e]">Export CSV</a>
        </div>

        <form method="GET" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
            <select name="pic_id" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua PIC</option>
                @foreach($pics as $pic)
                    <option value="{{ $pic->id }}" {{ (string) request('pic_id') === (string) $pic->id ? 'selected' : '' }}>{{ $pic->name }}</option>
                @endforeach
            </select>
            <select name="category_type" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category['key'] }}" {{ request('category_type') === $category['key'] ? 'selected' : '' }}>{{ $category['label'] }}</option>
                @endforeach
            </select>
            <select name="certificate_status" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua status sertifikat</option>
                <option value="generated" {{ request('certificate_status') === 'generated' ? 'selected' : '' }}>Hanya generated</option>
                <option value="missing" {{ request('certificate_status') === 'missing' ? 'selected' : '' }}>Belum generated</option>
            </select>
            <select name="source_channel" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                <option value="">Semua channel</option>
                <option value="voucher" {{ request('source_channel') === 'voucher' ? 'selected' : '' }}>Voucher PIC</option>
                <option value="direct" {{ request('source_channel') === 'direct' ? 'selected' : '' }}>Direct web</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, IG, token, kode" class="rounded-2xl border border-stone-300 px-4 py-3 text-sm xl:col-span-2">
            <div class="xl:col-span-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-full bg-emerald-800 px-5 py-3 text-sm font-semibold text-amber-100">Terapkan Filter</button>
                <a href="{{ route('admin.claims.index') }}" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">Reset</a>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-[1.8rem] bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-stone-100 text-stone-600">
                    <tr>
                        <th class="px-5 py-4 font-semibold">Peserta</th>
                        <th class="px-5 py-4 font-semibold">Kategori</th>
                        <th class="px-5 py-4 font-semibold">PIC</th>
                        <th class="px-5 py-4 font-semibold">Nominal</th>
                        <th class="px-5 py-4 font-semibold">Sertifikat</th>
                        <th class="px-5 py-4 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($claims as $claim)
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-stone-950">{{ $claim->name }}</p>
                                <p class="text-stone-500">{{ $claim->email }}</p>
                                <p class="text-xs text-stone-400">{{ $claim->instagram_username ? '@' . ltrim($claim->instagram_username, '@') : '-' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold text-stone-950">{{ $claim->display_category_label }}</p>
                                <p class="mt-1">
                                    <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-semibold {{ $claim->initial_voucher_id ? 'bg-stone-100 text-stone-700' : 'bg-sky-100 text-sky-700' }}">
                                        {{ $claim->initial_voucher_id ? 'Voucher PIC' : 'Direct web' }}
                                    </span>
                                </p>
                            </td>
                            <td class="px-5 py-4 text-stone-700">{{ $claim->pic?->name ?? $claim->initialVoucher?->pic?->name ?? '-' }}</td>
                            <td class="px-5 py-4 font-semibold text-stone-950">Rp {{ number_format($claim->total_donation_amount, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $claim->certificate_generated_at ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-600' }}">
                                    {{ $claim->certificate_generated_at ? 'Generated' : 'Belum' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.claims.show', $claim->id) }}" class="rounded-full border border-stone-300 px-3 py-2 text-xs font-semibold text-stone-700">Detail</a>
                                    <a href="{{ route('admin.claims.certificate', $claim->id) }}" class="rounded-full bg-stone-950 px-3 py-2 text-xs font-semibold text-amber-100">Unduh</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-stone-500">Belum ada kontribusi yang cocok dengan filter ini.</td>
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
