@extends('layouts.public')

@section('title', 'Sertifikat Apresiasi')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    {{-- Hero --}}
    <section class="overflow-hidden rounded-[2.2rem] bg-gradient-to-br from-emerald-950 via-emerald-900 to-amber-700 text-amber-50 shadow-[0_28px_80px_rgba(6,78,59,0.3)]">
        <div class="grid gap-6 px-6 py-8 md:grid-cols-[1.1fr_0.9fr] md:px-8">
            <div>
                <p class="text-sm uppercase tracking-[0.28em] text-amber-200/80">Kontribusi Tercatat</p>
                <h2 class="display-font mt-3 text-4xl leading-tight">Sertifikat apresiasi Anda sudah siap diunduh.</h2>
                <p class="mt-4 max-w-xl text-sm leading-7 text-amber-50/80">
                    Terima kasih, {{ $claim->name }}. Sistem telah mencatat kontribusi Anda pada program
                   PARQOUR 1447
                    dan menghasilkan kartu penghargaan digital yang bisa diunduh kapan saja.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ $downloadRoute }}" class="rounded-full bg-amber-300 px-5 py-3 text-sm font-semibold text-stone-950 transition hover:bg-amber-200">Unduh Sertifikat</a>
                    <a href="{{ route('public.contribute') }}" class="rounded-full border border-white/25 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">Buat Kontribusi Baru</a>
                </div>
            </div>

            <div class="rounded-[1.8rem] border border-white/10 bg-white/10 p-5 backdrop-blur">
                <p class="text-sm font-semibold">Ringkasan Cepat</p>
                <div class="mt-4 space-y-4 text-sm">
                    <div>
                        <p class="text-amber-100/70">Kategori</p>
                        <p class="mt-1 text-xl font-bold">{{ $claim->display_category_label }}</p>
                    </div>
                    <div>
                        <p class="text-amber-100/70">Nominal Kontribusi</p>
                        <p class="mt-1 text-xl font-bold">Rp {{ number_format($claim->total_donation_amount, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-amber-100/70">Channel</p>
                        <p class="mt-1 font-semibold">{{ $sourceLabel }}</p>
                    </div>
                    <div>
                        <p class="text-amber-100/70">PIC / Channel Owner</p>
                        <p class="mt-1 font-semibold">{{ $claim->pic?->name ?? $claim->initialVoucher?->pic?->name ?? config('qurban.default_pic_label') }}</p>
                    </div>
                    <div>
                        <p class="text-amber-100/70">Instagram</p>
                        <p class="mt-1 font-semibold">{{ $claim->instagram_username ? '@' . ltrim($claim->instagram_username, '@') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Status Sertifikat (full width, komisi dihapus) --}}
    <section>
        <div class="rounded-[1.8rem] border border-stone-200 bg-white p-6 shadow-sm">
            <p class="text-sm uppercase tracking-[0.24em] text-stone-500">Status Sertifikat</p>
            <div class="mt-4 flex items-center gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-xs font-bold uppercase tracking-[0.22em] text-emerald-900">PDF</div>
                <div>
                    <p class="text-lg font-bold text-stone-950">Generated</p>
                    <p class="text-sm text-stone-500">{{ optional($claim->certificate_generated_at)->format('d M Y H:i') }}</p>
                </div>
            </div>
            <p class="mt-4 text-sm leading-7 text-stone-600">Admin dapat melihat dan mengunduh sertifikat ini dari dashboard internal. Simpan link halaman ini untuk akses ulang.</p>
            <div class="mt-4 rounded-2xl bg-stone-100 px-4 py-3 text-xs text-stone-500">
                {{ url()->current() }}
            </div>
        </div>
    </section>

    {{-- Snapshot transaksi (tanpa kolom komisi dan tanpa "kampanye aktif") --}}
    <section>
        <div class="rounded-[1.8rem] border border-stone-200 bg-white p-6 shadow-sm">
            <p class="text-sm uppercase tracking-[0.24em] text-stone-500">Snapshot Transaksi Anda</p>
            <dl class="mt-5 space-y-4 text-sm">
                <div>
                    <dt class="text-stone-500">Kategori tercatat</dt>
                    <dd class="mt-1 font-semibold text-stone-950">{{ $settingsAudit['snapshot']['category_label'] }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Basis pricing saat transaksi</dt>
                    <dd class="mt-1 font-semibold text-stone-950">{{ $settingsAudit['snapshot']['pricing_basis_label'] }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Harga acuan saat transaksi</dt>
                    <dd class="mt-1 font-semibold text-stone-950">Rp {{ number_format($settingsAudit['snapshot']['unit_price'], 0, ',', '.') }}</dd>
                </div>
                <div>
                    <dt class="text-stone-500">Kontribusi tercatat</dt>
                    <dd class="mt-1 font-semibold text-stone-950">Rp {{ number_format($settingsAudit['snapshot']['contribution_amount'], 0, ',', '.') }}</dd>
                </div>
                @if(!empty($settingsAudit['snapshot']['transfer_destination']))
                    <div>
                        <dt class="text-stone-500">Tujuan transfer saat transaksi</dt>
                        <dd class="mt-1 font-semibold text-stone-950">{{ $settingsAudit['snapshot']['transfer_destination'] }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-stone-500">Waktu pencatatan</dt>
                    <dd class="mt-1 font-semibold text-stone-950">{{ optional($claim->created_at)->format('d M Y H:i') ?: '-' }}</dd>
                </div>
            </dl>

            @if($claim->category_type === 'PATUNGAN')
                <div class="mt-6 pt-5 border-t border-stone-100">
                    <div class="mb-2 flex items-center justify-between text-sm">
                        <span class="text-stone-600">Progress terhadap target {{ $claim->patungan_target === 'SAPI' ? 'Sapi' : 'Domba' }}</span>
                        <span class="font-semibold text-stone-900">{{ $claim->patungan_progress_percent }}%</span>
                    </div>
                    <div class="h-3 rounded-full bg-stone-200">
                        <div class="h-3 rounded-full bg-emerald-700" style="width: {{ $claim->patungan_progress_percent }}%"></div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>
@endsection
