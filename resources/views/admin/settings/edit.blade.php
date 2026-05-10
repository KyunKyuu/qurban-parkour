@extends('layouts.admin')

@section('title', 'Settings Kurban')

@section('content')
<div class="space-y-6">
    <section class="rounded-[2rem] bg-stone-950 px-6 py-7 text-amber-100 shadow-[0_30px_80px_rgba(28,25,23,0.2)]">
        <p class="text-sm uppercase tracking-[0.28em] text-amber-300/80">Operational Settings</p>
        <div class="mt-3 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="display-font text-4xl leading-tight">Atur kampanye, channel default, dan pricing kurban.</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-amber-100/75">
                    Perubahan di halaman ini akan dipakai untuk kontribusi baru. Claim yang sudah tercatat tetap memakai snapshot nominal dan data yang tersimpan saat transaksi dibuat.
                </p>
            </div>
            <a href="{{ route('public.contribute') }}" class="inline-flex items-center justify-center rounded-full bg-amber-300 px-5 py-3 text-sm font-semibold text-stone-950">
                Lihat Form Publik
            </a>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.settings.qurban.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-[1.8rem] bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm uppercase tracking-[0.24em] text-stone-500">Kampanye</p>
                        <h3 class="mt-1 text-xl font-bold text-stone-950">Identitas publik dan status periode</h3>
                    </div>
                    <label class="inline-flex items-center gap-3 rounded-full border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-700">
                        <input type="checkbox" name="claim_open" value="1" class="h-4 w-4 rounded border-stone-300 text-emerald-700 focus:ring-emerald-700" {{ old('claim_open', $settings['claim_open']) ? 'checked' : '' }}>
                        Kontribusi dibuka
                    </label>
                </div>

                <div class="mt-5 grid gap-4">
                    <div>
                        <label for="campaign_name" class="mb-2 block text-sm font-semibold text-stone-700">Nama Kampanye</label>
                        <input id="campaign_name" name="campaign_name" type="text" value="{{ old('campaign_name', $settings['campaign_name']) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                        @error('campaign_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="campaign_subtitle" class="mb-2 block text-sm font-semibold text-stone-700">Subtitle Kampanye</label>
                        <textarea id="campaign_subtitle" name="campaign_subtitle" rows="3" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">{{ old('campaign_subtitle', $settings['campaign_subtitle']) }}</textarea>
                        @error('campaign_subtitle') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="campaign_tagline" class="mb-2 block text-sm font-semibold text-stone-700">Tagline Internal</label>
                        <textarea id="campaign_tagline" name="campaign_tagline" rows="3" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">{{ old('campaign_tagline', $settings['campaign_tagline']) }}</textarea>
                        @error('campaign_tagline') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="closing_at" class="mb-2 block text-sm font-semibold text-stone-700">Tanggal Penutupan Sistem</label>
                            <input id="closing_at" name="closing_at" type="datetime-local" value="{{ old('closing_at', $settings['closing_at']) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                            @error('closing_at') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="closing_label" class="mb-2 block text-sm font-semibold text-stone-700">Label Penutupan</label>
                            <input id="closing_label" name="closing_label" type="text" value="{{ old('closing_label', $settings['closing_label']) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                            @error('closing_label') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[1.8rem] bg-white p-6 shadow-sm">
                <p class="text-sm uppercase tracking-[0.24em] text-stone-500">Operasional</p>
                <h3 class="mt-1 text-xl font-bold text-stone-950">PIC default, rekening, dan sertifikat</h3>

                <div class="mt-5 grid gap-4">
                    <div>
                        <label for="default_pic_name" class="mb-2 block text-sm font-semibold text-stone-700">PIC Default Lookup</label>
                        <input id="default_pic_name" name="default_pic_name" type="text" value="{{ old('default_pic_name', $settings['default_pic_name']) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                        @error('default_pic_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="default_pic_label" class="mb-2 block text-sm font-semibold text-stone-700">Label PIC Default</label>
                        <input id="default_pic_label" name="default_pic_label" type="text" value="{{ old('default_pic_label', $settings['default_pic_label']) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                        @error('default_pic_label') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="default_pic_email" class="mb-2 block text-sm font-semibold text-stone-700">Email PIC Default</label>
                        <input id="default_pic_email" name="default_pic_email" type="email" value="{{ old('default_pic_email', $settings['default_pic_email']) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                        @error('default_pic_email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="bank_account_label" class="mb-2 block text-sm font-semibold text-stone-700">Tujuan Transfer</label>
                        <input id="bank_account_label" name="bank_account_label" type="text" value="{{ old('bank_account_label', $settings['bank_account_label']) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                        @error('bank_account_label') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="certificate_title" class="mb-2 block text-sm font-semibold text-stone-700">Judul Sertifikat</label>
                        <input id="certificate_title" name="certificate_title" type="text" value="{{ old('certificate_title', $settings['certificate_title']) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                        @error('certificate_title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="certificate_subtitle" class="mb-2 block text-sm font-semibold text-stone-700">Subtitle Sertifikat</label>
                        <textarea id="certificate_subtitle" name="certificate_subtitle" rows="3" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">{{ old('certificate_subtitle', $settings['certificate_subtitle']) }}</textarea>
                        @error('certificate_subtitle') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-[1.8rem] bg-white p-6 shadow-sm">
            <p class="text-sm uppercase tracking-[0.24em] text-stone-500">Pricing</p>
            <div class="mt-1 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <h3 class="text-xl font-bold text-stone-950">Harga, komisi, dan copy kategori</h3>
                <p class="text-sm text-stone-500">Patungan target dipilih dari kategori non-patungan.</p>
            </div>

            <div class="mt-5 rounded-[1.5rem] border border-stone-200 bg-stone-50 p-4">
                <p class="text-sm font-semibold text-stone-700">Kategori yang boleh menjadi target patungan</p>
                <div class="mt-3 flex flex-wrap gap-3">
                    @foreach($patunganTargetOptions as $targetKey)
                        <label class="inline-flex items-center gap-2 rounded-full border border-stone-300 bg-white px-4 py-2 text-sm text-stone-700">
                            <input type="checkbox" name="patungan_targets[]" value="{{ $targetKey }}" class="h-4 w-4 rounded border-stone-300 text-emerald-700 focus:ring-emerald-700" {{ in_array($targetKey, old('patungan_targets', $settings['patungan_targets'] ?? []), true) ? 'checked' : '' }}>
                            {{ $settings['categories'][$targetKey]['label'] ?? $targetKey }}
                        </label>
                    @endforeach
                </div>
                @error('patungan_targets') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                @error('patungan_targets.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="mt-5 grid gap-4 xl:grid-cols-2">
                @foreach($settings['categories'] as $key => $category)
                    <div class="rounded-[1.6rem] border border-stone-200 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.24em] text-stone-500">{{ $key }}</p>
                                <h4 class="mt-1 text-lg font-bold text-stone-950">{{ $category['label'] }}</h4>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4">
                            <div>
                                <label for="categories_{{ $key }}_label" class="mb-2 block text-sm font-semibold text-stone-700">Label</label>
                                <input id="categories_{{ $key }}_label" name="categories[{{ $key }}][label]" type="text" value="{{ old("categories.$key.label", $category['label']) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                                @error("categories.$key.label") <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="categories_{{ $key }}_description" class="mb-2 block text-sm font-semibold text-stone-700">Deskripsi</label>
                                <textarea id="categories_{{ $key }}_description" name="categories[{{ $key }}][description]" rows="3" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">{{ old("categories.$key.description", $category['description']) }}</textarea>
                                @error("categories.$key.description") <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label for="categories_{{ $key }}_price" class="mb-2 block text-sm font-semibold text-stone-700">Harga</label>
                                    <input id="categories_{{ $key }}_price" name="categories[{{ $key }}][price]" type="number" min="0" step="1" value="{{ old("categories.$key.price", $category['price']) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                                    @error("categories.$key.price") <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="categories_{{ $key }}_commission" class="mb-2 block text-sm font-semibold text-stone-700">Komisi</label>
                                    <input id="categories_{{ $key }}_commission" name="categories[{{ $key }}][commission]" type="number" min="0" step="1" value="{{ old("categories.$key.commission", $category['commission']) }}" class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                                    @error("categories.$key.commission") <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-stone-950 px-6 py-4 text-sm font-semibold uppercase tracking-[0.22em] text-amber-100 transition hover:bg-stone-800">
                Simpan Settings Kurban
            </button>
        </div>
    </form>
</div>
@endsection
