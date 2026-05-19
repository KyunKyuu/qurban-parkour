@extends('layouts.admin')

@section('title', 'Detail Kontribusi')

@section('content')
<div class="space-y-6">
    <section class="rounded-[1.8rem] bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.24em] text-stone-500">Participant Detail</p>
                <h2 class="mt-2 text-3xl font-bold text-stone-950">{{ $claim->name }}</h2>
                <p class="mt-2 text-sm text-stone-500">{{ $claim->display_category_label }} - {{ $claim->initialVoucher?->code ?? 'Direct web' }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.claims.certificate', $claim->id) }}" class="rounded-full bg-stone-950 px-5 py-3 text-sm font-semibold text-amber-100">Unduh Sertifikat</a>
                <a href="{{ route('admin.claims.index') }}" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">Kembali</a>
            </div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1fr_0.95fr]">
        <div class="space-y-6">
            <div class="rounded-[1.8rem] bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-stone-950">Ringkasan Kontribusi</h3>
                <dl class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="rounded-[1.2rem] bg-stone-100 p-4">
                        <dt class="text-xs uppercase tracking-[0.24em] text-stone-500">Kategori</dt>
                        <dd class="mt-2 text-lg font-bold text-stone-950">{{ $claim->display_category_label }}</dd>
                    </div>
                    <div class="rounded-[1.2rem] bg-stone-100 p-4">
                        <dt class="text-xs uppercase tracking-[0.24em] text-stone-500">Nominal</dt>
                        <dd class="mt-2 text-lg font-bold text-stone-950">Rp {{ number_format($claim->total_donation_amount, 0, ',', '.') }}</dd>
                    </div>
                    <div class="rounded-[1.2rem] bg-stone-100 p-4">
                        <dt class="text-xs uppercase tracking-[0.24em] text-stone-500">PIC</dt>
                        <dd class="mt-2 text-lg font-bold text-stone-950">{{ $claim->pic?->name ?? $claim->initialVoucher?->pic?->name ?? '-' }}</dd>
                    </div>
                    <div class="rounded-[1.2rem] bg-stone-100 p-4">
                        <dt class="text-xs uppercase tracking-[0.24em] text-stone-500">Metode Bayar</dt>
                        <dd class="mt-2 text-lg font-bold text-stone-950">{{ strtoupper($claim->payment_method ?? 'cash') }}</dd>
                    </div>
                </dl>

            </div>

            <div class="rounded-[1.8rem] bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-stone-950">Status Sertifikat</h3>
                <div class="mt-5 rounded-[1.2rem] bg-emerald-50 p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-emerald-700">Sertifikat</p>
                    <p class="mt-2 text-lg font-bold text-stone-950">{{ $claim->certificate_generated_at ? 'Generated' : 'Belum dibuat' }}</p>
                    <p class="mt-2 text-sm text-stone-600">{{ optional($claim->certificate_generated_at)->format('d M Y H:i') ?: '-' }}</p>
                </div>
            </div>

        </div>

        <div class="space-y-6">
            <div class="rounded-[1.8rem] bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-stone-950">Kontak Peserta</h3>
                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="text-stone-500">Email</dt>
                        <dd class="mt-1 font-semibold text-stone-950">{{ $claim->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">WhatsApp</dt>
                        <dd class="mt-1 font-semibold text-stone-950">{{ $claim->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Instagram</dt>
                        <dd class="mt-1 font-semibold text-stone-950">{{ $claim->instagram_username ? '@' . ltrim($claim->instagram_username, '@') : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Tujuan Transfer</dt>
                        <dd class="mt-1 font-semibold text-stone-950">{{ $claim->transfer_destination ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-stone-500">Bukti Transfer</dt>
                        <dd class="mt-1">
                            @if($claim->transfer_proof_path)
                                <a href="{{ Storage::url($claim->transfer_proof_path) }}" target="_blank" class="block">
                                    <img src="{{ Storage::url($claim->transfer_proof_path) }}"
                                         alt="Bukti Transfer"
                                         class="mt-2 max-h-72 w-full rounded-2xl object-contain border border-stone-200 bg-stone-50 cursor-zoom-in">
                                </a>
                                <a href="{{ Storage::url($claim->transfer_proof_path) }}" target="_blank"
                                   class="mt-2 inline-block text-xs text-emerald-700 underline hover:text-emerald-900">
                                    Buka gambar penuh →
                                </a>
                            @else
                                <span class="text-stone-400">-</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-[1.8rem] bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-stone-950">Edit Data Ringan</h3>
                <form method="POST" action="{{ route('admin.claims.update', $claim->id) }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ old('name', $claim->name) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm" placeholder="Nama">
                    <input type="email" name="email" value="{{ old('email', $claim->email) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm" placeholder="Email">
                    <input type="text" name="phone" value="{{ old('phone', $claim->phone) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm" placeholder="WhatsApp">
                    <input type="text" name="instagram_username" value="{{ old('instagram_username', $claim->instagram_username) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm" placeholder="Instagram">
                    <select name="payment_method" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                        <option value="cash" {{ old('payment_method', $claim->payment_method) === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="transfer" {{ old('payment_method', $claim->payment_method) === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                    <input type="text" name="transfer_destination" value="{{ old('transfer_destination', $claim->transfer_destination) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm" placeholder="Tujuan transfer">
                    <button type="submit" class="rounded-full bg-emerald-800 px-5 py-3 text-sm font-semibold text-amber-100">Simpan Perubahan</button>
                </form>
            </div>

            <div class="rounded-[1.8rem] border border-red-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-stone-950">Hapus Data</h3>
                <p class="mt-3 text-sm leading-7 text-stone-600">Gunakan hanya jika data kontribusi ini memang harus dipensiunkan dari dashboard.</p>
                <form method="POST" action="{{ route('admin.claims.destroy', $claim->id) }}" class="mt-4" onsubmit="return confirm('Hapus data kontribusi ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-full bg-red-600 px-5 py-3 text-sm font-semibold text-white">Hapus Kontribusi</button>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
