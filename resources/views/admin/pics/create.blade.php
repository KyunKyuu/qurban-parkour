@extends('layouts.admin')

@section('title', isset($pic) ? 'Edit PIC' : 'Tambah PIC')

@section('content')
@php
    $currentType = old('pic_type', $pic->pic_type ?? 'kasie');
    $currentCommunityId = old('community_id',
        isset($pic) && $pic->isKomunitas() ? $pic->communityAsPicKomunitas?->id : null
    );
    $currentSubordinateIds = old('subordinate_ids',
        isset($pic) && $pic->isKasie()
            ? $pic->communities->pluck('pic_komunitas_id')->filter()->values()->toArray()
            : []
    );
@endphp

<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.pics.index') }}" class="text-emerald-600 hover:text-emerald-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-emerald-950">{{ isset($pic) ? 'Edit PIC' : 'Tambah PIC Baru' }}</h2>
            <p class="text-emerald-600 text-sm">{{ isset($pic) ? 'Update informasi PIC' : 'Tambahkan PIC untuk distribusi voucher' }}</p>
        </div>
    </div>

    <div class="rounded-[1.8rem] bg-white border border-emerald-100 shadow-sm p-6">
        <form action="{{ isset($pic) ? route('admin.pics.update', $pic) : route('admin.pics.store') }}" method="POST">
            @csrf
            @if(isset($pic)) @method('PUT') @endif

            <div class="space-y-5">

                {{-- Name + Level side by side --}}
                <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-4 items-start">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-emerald-800 mb-1">
                            Nama <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name"
                            value="{{ old('name', $pic->name ?? '') }}"
                            class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm @error('name') border-red-500 @enderror"
                            required placeholder="Nama lengkap PIC">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="pic_type" class="block text-sm font-semibold text-emerald-800 mb-1">Level</label>
                        <select name="pic_type" id="pic_type" onchange="handleTypeChange(this.value)"
                            class="rounded-2xl border border-stone-300 px-4 py-3 text-sm font-semibold @error('pic_type') border-red-500 @enderror">
                            <option value="kasie"     {{ $currentType === 'kasie'     ? 'selected' : '' }}>PIC Kasie</option>
                            <option value="komunitas" {{ $currentType === 'komunitas' ? 'selected' : '' }}>PIC Komunitas</option>
                        </select>
                        @error('pic_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Code + Active --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="code" class="block text-sm font-semibold text-emerald-800 mb-1">Kode PIC</label>
                        <input type="text" name="code" id="code"
                            value="{{ old('code', $pic->code ?? '') }}"
                            placeholder="Contoh: KASIE01"
                            class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm font-mono @error('code') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-stone-400">Opsional. Kode unik untuk identifikasi.</p>
                        @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end pb-3">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $pic->is_active ?? true) ? 'checked' : '' }}
                                class="w-4 h-4 text-emerald-600 border-stone-300 rounded">
                            <span class="text-sm font-medium text-emerald-900">PIC Aktif</span>
                        </label>
                    </div>
                </div>

                {{-- Dynamic section: Kasie picks subordinates / Komunitas picks community --}}
                <div id="section-kasie" class="{{ $currentType === 'kasie' ? '' : 'hidden' }}">
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-700 mb-2">Bawahan PIC Kasie</p>
                        <p class="text-xs text-amber-700 mb-3">Pilih PIC Komunitas yang dikelola oleh Kasie ini. Satu PIC Komunitas hanya bisa punya satu Kasie.</p>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($picKomunitas as $pk)
                                @php
                                    $isChecked = in_array($pk->id, (array) $currentSubordinateIds);
                                    $currentKasie = $pk->communityAsPicKomunitas?->pic;
                                    $takenByOther = $currentKasie && (!isset($pic) || $currentKasie->id !== $pic->id);
                                @endphp
                                <label class="flex items-center gap-3 {{ $takenByOther ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}">
                                    <input type="checkbox" name="subordinate_ids[]" value="{{ $pk->id }}"
                                        {{ $isChecked ? 'checked' : '' }}
                                        {{ $takenByOther ? 'disabled' : '' }}
                                        class="w-4 h-4 text-emerald-600 border-stone-300 rounded">
                                    <span class="text-sm text-stone-800">
                                        {{ $pk->name }}
                                        @if($pk->communityAsPicKomunitas)
                                            <span class="text-stone-400">— {{ $pk->communityAsPicKomunitas->name }}</span>
                                        @endif
                                        @if($takenByOther)
                                            <span class="text-xs text-amber-600">(sudah di bawah {{ $currentKasie->name }})</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                            @if($picKomunitas->isEmpty())
                                <p class="text-sm text-stone-400 italic">Belum ada PIC Komunitas yang tersedia.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div id="section-komunitas" class="{{ $currentType === 'komunitas' ? '' : 'hidden' }}">
                    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-sky-700 mb-2">Komunitas yang Dikelola</p>
                        <p class="text-xs text-sky-700 mb-3">PIC Komunitas hanya mengelola satu komunitas langsung.</p>
                        <select name="community_id" id="community_id"
                            class="w-full rounded-xl border border-stone-300 px-4 py-3 text-sm @error('community_id') border-red-500 @enderror">
                            <option value="">-- Pilih komunitas --</option>
                            @foreach($communities as $community)
                                <option value="{{ $community->id }}"
                                    {{ $currentCommunityId == $community->id ? 'selected' : '' }}>
                                    {{ $community->name }} ({{ $community->code }})
                                    @if($community->pic) · Kasie: {{ $community->pic->name }}@endif
                                </option>
                            @endforeach
                        </select>
                        @error('community_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Login credentials --}}
                <div class="border-t border-emerald-100 pt-5">
                    <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-500 mb-4">Kredensial Login</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-semibold text-emerald-800 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email"
                                value="{{ old('email', $pic->email ?? '') }}"
                                class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm @error('email') border-red-500 @enderror"
                                required>
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-semibold text-emerald-800 mb-1">
                                    Password {!! isset($pic) ? '<span class="text-stone-400 font-normal text-xs">(kosongkan jika tidak berubah)</span>' : '<span class="text-red-500">*</span>' !!}
                                </label>
                                <input type="password" name="password" id="password"
                                    class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm @error('password') border-red-500 @enderror"
                                    {{ isset($pic) ? '' : 'required' }}>
                                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="block text-sm font-semibold text-emerald-800 mb-1">
                                    Konfirmasi Password {!! isset($pic) ? '' : '<span class="text-red-500">*</span>' !!}
                                </label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm"
                                    {{ isset($pic) ? '' : 'required' }}>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-emerald-100">
                <a href="{{ route('admin.pics.index') }}"
                    class="rounded-full px-5 py-2.5 text-sm font-semibold border border-stone-300 text-stone-700 hover:border-emerald-600 transition">
                    Batal
                </a>
                <button type="submit"
                    class="rounded-full bg-emerald-700 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 transition">
                    {{ isset($pic) ? 'Simpan Perubahan' : 'Tambah PIC' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function handleTypeChange(value) {
    document.getElementById('section-kasie').classList.toggle('hidden', value !== 'kasie');
    document.getElementById('section-komunitas').classList.toggle('hidden', value !== 'komunitas');
}
</script>
@endsection
