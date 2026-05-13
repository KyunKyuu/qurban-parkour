@extends('layouts.public')

@section('title', 'Form Kurban — PARQOUR')

@section('content')
<div class="mx-auto max-w-2xl">

    {{-- Header --}}
    <div class="mb-8 text-center">
        <span class="text-xs font-semibold uppercase tracking-[0.26em] text-stone-400">Idul Adha 1447 H · 27 Mei 2026</span>
        <h1 class="display-font mt-3 text-4xl tracking-tight text-stone-950 md:text-5xl">Form Kurban</h1>
        <p class="mt-3 text-sm leading-relaxed text-stone-500">{{ $campaignSubtitle }}</p>
    </div>

    {{-- Error alerts --}}
    @if(session('error'))
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
        <ul class="space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('public.claim.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <input type="hidden" name="code" value="{{ $code }}">

        {{-- ── Data Diri ── --}}
        <div class="rounded-[1.75rem] border border-stone-200 bg-white p-6 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
            <p class="mb-5 text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Data Diri</p>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1.5 block text-sm font-semibold text-stone-700">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                        class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-[#1b4332] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b4332]/10"
                        placeholder="Nama peserta kurban">
                </div>
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-semibold text-stone-700">Email <span class="text-red-500">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                        class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-[#1b4332] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b4332]/10"
                        placeholder="nama@email.com">
                </div>
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-semibold text-stone-700">Nomor WhatsApp <span class="text-red-500">*</span></label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required
                        class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-[#1b4332] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b4332]/10"
                        placeholder="08xxxxxxxxxx">
                </div>
                <div>
                    <label for="instagram_username" class="mb-1.5 block text-sm font-semibold text-stone-700">Instagram</label>
                    <input id="instagram_username" name="instagram_username" type="text" value="{{ old('instagram_username') }}"
                        class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-[#1b4332] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b4332]/10"
                        placeholder="@username (opsional)">
                </div>
            </div>
        </div>

        {{-- ── Kategori Kurban ── --}}
        <div class="rounded-[1.75rem] border border-stone-200 bg-white p-6 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
            <p class="mb-5 text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Kategori Kurban</p>
            <div class="space-y-2.5">
                @foreach($categoryOptions as $category)
                @php
                    $isSelected = old('category_type', 'DOMBA') === $category['key'];
                    $isPatungan = $category['key'] === 'PATUNGAN';
                    $displayPrice = $category['price'] > 0
                        ? 'Rp ' . number_format($category['price'], 0, ',', '.')
                        : 'Nominal bebas';
                @endphp
                <div>
                    <input
                        type="radio"
                        id="cat-{{ $category['key'] }}"
                        name="category_type"
                        value="{{ $category['key'] }}"
                        class="peer sr-only category-radio"
                        {{ $isSelected ? 'checked' : '' }}>
                    <label for="cat-{{ $category['key'] }}" class="group cursor-pointer block rounded-xl border border-stone-200 bg-stone-50 px-4 py-3.5 transition
                        peer-checked:border-[#1b4332] peer-checked:bg-[#1b4332]/5
                        hover:border-stone-300">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-bold text-stone-900">{{ $category['label'] }}</p>
                                <p class="mt-0.5 text-sm leading-relaxed text-stone-500">{{ $category['description'] }}</p>
                            </div>
                            <span class="flex-shrink-0 rounded-lg bg-white border border-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-700 shadow-sm">
                                {{ $displayPrice }}
                            </span>
                        </div>
                    </label>

                    @if($isPatungan)
                    <div class="hidden peer-checked:block mt-2 rounded-[1.5rem] border border-amber-200 bg-amber-50 p-5">
                        <p class="mb-4 text-xs font-semibold uppercase tracking-[0.24em] text-amber-700">Detail Patungan</p>
                        <div>
                            <label for="contribution_amount_display" class="mb-1.5 block text-sm font-semibold text-stone-700">Nominal Kontribusi <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-sm text-stone-400">Rp</span>
                                <input id="contribution_amount_display" type="text" inputmode="numeric"
                                    value="{{ old('contribution_amount') ? 'Rp ' . number_format(old('contribution_amount'), 0, ',', '.') : '' }}"
                                    class="w-full rounded-xl border border-stone-200 bg-white py-3 pl-10 pr-4 text-sm transition focus:border-[#1b4332] focus:outline-none focus:ring-2 focus:ring-[#1b4332]/10"
                                    placeholder="0">
                                <input id="contribution_amount" name="contribution_amount" type="hidden"
                                    value="{{ old('contribution_amount') }}">
                            </div>
                        </div>
                        <p class="mt-3 text-xs leading-relaxed text-amber-700/80">Nominal bebas seikhlasnya. Admin akan mengelola alokasi ke hewan kurban.</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- ── Pembayaran ── --}}
        <div class="rounded-[1.75rem] border border-stone-200 bg-white p-6 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
            <p class="mb-5 text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Metode Pembayaran</p>

            {{-- Rekening --}}
            <div class="mb-5 flex items-center justify-between gap-4 rounded-xl bg-stone-50 border border-stone-200 px-4 py-3.5">
                <div>
                    <p class="text-xs text-stone-400">Rekening tujuan transfer</p>
                    <p class="mt-0.5 text-sm font-semibold text-stone-800" id="rekening-label">{{ $bankAccountLabel }}</p>
                </div>
                <button type="button" onclick="copyRekening()" id="copy-rek-btn"
                    class="flex-shrink-0 rounded-lg border border-stone-200 bg-white px-3 py-1.5 text-xs font-semibold text-stone-600 transition hover:border-[#1b4332] hover:text-[#1b4332]">
                    Salin
                </button>
            </div>

            {{-- Metode toggle --}}
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="payment_method" value="transfer"
                        class="peer sr-only payment-method" {{ old('payment_method', 'transfer') === 'transfer' ? 'checked' : '' }}>
                    <div class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-center text-sm font-semibold transition
                        peer-checked:border-[#1b4332] peer-checked:bg-[#1b4332] peer-checked:text-amber-100">
                        Transfer
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="payment_method" value="cash"
                        class="peer sr-only payment-method" {{ old('payment_method', 'transfer') === 'cash' ? 'checked' : '' }}>
                    <div class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-center text-sm font-semibold transition
                        peer-checked:border-[#1b4332] peer-checked:bg-[#1b4332] peer-checked:text-amber-100">
                        Cash / Tunai
                    </div>
                </label>
            </div>

            {{-- Transfer panel --}}
            <div id="transferPanel" class="mt-4 space-y-4">
                <div>
                    <label for="transfer_destination" class="mb-1.5 block text-sm font-semibold text-stone-700">Tujuan Transfer</label>
                    <input id="transfer_destination" name="transfer_destination" type="text"
                        value="{{ old('transfer_destination', $bankAccountLabel) }}"
                        class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm transition focus:border-[#1b4332] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1b4332]/10">
                </div>
                <div>
                    <label for="transfer_proof" class="mb-1.5 block text-sm font-semibold text-stone-700">Bukti Transfer</label>
                    <label for="transfer_proof" class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-stone-300 bg-stone-50 px-4 py-6 text-center transition hover:border-[#1b4332] hover:bg-[#1b4332]/5" id="proof-label">
                        <svg class="h-6 w-6 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="text-sm text-stone-500" id="proof-text">Klik untuk upload bukti transfer</span>
                        <span class="text-xs text-stone-400">JPG, PNG, atau PDF · Maks. 4 MB</span>
                        <input id="transfer_proof" name="transfer_proof" type="file"
                            accept=".jpg,.jpeg,.png,.pdf,image/*,application/pdf"
                            class="sr-only" onchange="updateProofLabel(this)">
                    </label>
                </div>
            </div>
        </div>

        {{-- ── Info Voucher & Komunitas ── --}}
        <div class="rounded-[1.75rem] border border-stone-200 bg-white p-5 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-stone-400">Kode Kontribusi</p>
            <p class="mt-2 font-mono text-lg font-semibold text-stone-900">{{ $code }}</p>
            @if($communityLabel)
                <p class="mt-1.5 text-sm text-stone-600">
                    Komunitas: <span class="font-semibold text-stone-800">{{ $communityLabel }}</span>
                </p>
            @endif
            <p class="mt-0.5 text-sm text-stone-500">
                PIC: <span class="font-medium text-stone-700">{{ $picLabel }}</span>
            </p>
        </div>

        {{-- ── Submit ── --}}
        <button type="submit"
            class="w-full rounded-2xl bg-[#1b4332] py-4 text-sm font-bold uppercase tracking-[0.18em] text-amber-100 shadow-[0_8px_24px_rgba(27,67,50,0.28)] transition hover:bg-[#0f2d1e] hover:-translate-y-0.5 active:scale-[0.98] active:translate-y-0">
            Kirim Kontribusi Kurban
        </button>

        <p class="text-center text-xs text-stone-400">
            Setelah submit, sertifikat apresiasi digital akan tersedia untuk diunduh.
        </p>
    </form>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const paymentRadios = document.querySelectorAll('.payment-method');
    const transferPanel = document.getElementById('transferPanel');

    function toggleTransfer() {
        const sel = document.querySelector('.payment-method:checked');
        transferPanel.classList.toggle('hidden', !sel || sel.value !== 'transfer');
    }

    paymentRadios.forEach(r => r.addEventListener('change', toggleTransfer));
    toggleTransfer();

    window.copyRekening = function () {
        const text = document.getElementById('rekening-label').textContent.trim();
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('copy-rek-btn');
            btn.textContent = 'Tersalin!';
            btn.classList.add('border-[#1b4332]', 'text-[#1b4332]');
            setTimeout(() => {
                btn.textContent = 'Salin';
                btn.classList.remove('border-[#1b4332]', 'text-[#1b4332]');
            }, 2000);
        });
    };

    window.updateProofLabel = function (input) {
        const label = document.getElementById('proof-text');
        if (input.files && input.files[0]) {
            label.textContent = input.files[0].name;
            label.classList.add('font-semibold', 'text-[#1b4332]');
        }
    };

    const displayInput = document.getElementById('contribution_amount_display');
    const hiddenInput = document.getElementById('contribution_amount');

    if (displayInput && hiddenInput) {
        displayInput.addEventListener('input', function () {
            const raw = this.value.replace(/\D/g, '');
            hiddenInput.value = raw;
            this.value = raw ? raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        });

        displayInput.addEventListener('blur', function () {
            const raw = hiddenInput.value;
            if (raw) {
                this.value = raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
        });
    }
})();
</script>
@endsection
