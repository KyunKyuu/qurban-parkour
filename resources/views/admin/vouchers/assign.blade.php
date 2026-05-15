@extends('layouts.admin')

@section('title', 'Assign Voucher')

@section('content')
<div class="max-w-2xl space-y-6">

    <div>
        <h2 class="text-2xl font-bold text-emerald-950">Assign Voucher ke PIC</h2>
        <p class="text-emerald-600 text-sm">Voucher dapat di-assign ke PIC Komunitas atau langsung ke PIC Kasie.</p>
    </div>

    <div class="rounded-[1.6rem] bg-emerald-50 border border-emerald-200 px-5 py-4">
        <p class="text-sm font-medium text-emerald-900">
            Voucher tersedia (belum di-assign):
            <span class="text-xl font-extrabold text-emerald-700 ml-1">{{ $availableCount }}</span>
        </p>
    </div>

    <div class="rounded-[1.8rem] bg-white border border-emerald-100 shadow-sm p-6">
        <form method="POST" action="{{ route('admin.vouchers.assign') }}" class="space-y-5">
            @csrf

            {{-- PIC Selection --}}
            <div>
                <label for="pic_id" class="block text-sm font-semibold text-emerald-800 mb-1">
                    Pilih PIC <span class="text-red-500">*</span>
                </label>
                <select name="pic_id" id="pic_id" required
                    class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm @error('pic_id') border-red-500 @enderror">
                    <option value="">-- Pilih PIC --</option>
                    @if($picKasie->isNotEmpty())
                        <optgroup label="PIC Kasie (langsung)">
                            @foreach($picKasie as $pic)
                                <option value="{{ $pic->id }}" {{ old('pic_id') == $pic->id ? 'selected' : '' }}>
                                    {{ $pic->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if($picKomunitas->isNotEmpty())
                        <optgroup label="PIC Komunitas">
                            @foreach($picKomunitas as $pic)
                                <option value="{{ $pic->id }}" {{ old('pic_id') == $pic->id ? 'selected' : '' }}>
                                    {{ $pic->name }}
                                    @if($pic->communityAsPicKomunitas)
                                        — {{ $pic->communityAsPicKomunitas->name }}
                                    @else
                                        (belum ada komunitas)
                                    @endif
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
                <p class="mt-1 text-xs text-stone-400">Komunitas otomatis mengikuti PIC Komunitas. Untuk PIC Kasie, voucher di-assign langsung tanpa komunitas.</p>
                @error('pic_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            @if($picKasie->isEmpty() && $picKomunitas->isEmpty())
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Belum ada PIC aktif.
                    <a href="{{ route('admin.pics.create') }}" class="underline font-semibold">Tambah PIC</a>
                </div>
            @endif

            {{-- Batch --}}
            <div>
                <label for="batch_id" class="block text-sm font-semibold text-emerald-800 mb-1">Filter Batch (Opsional)</label>
                <select name="batch_id" id="batch_id"
                    class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                    <option value="">-- Semua Batch --</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
                            {{ $batch->name }} ({{ $batch->generated_count }} voucher)
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-stone-400">Kosongkan untuk assign dari semua batch.</p>
            </div>

            {{-- Quantity --}}
            <div>
                <label for="qty" class="block text-sm font-semibold text-emerald-800 mb-1">
                    Jumlah Voucher <span class="text-red-500">*</span>
                </label>
                <input type="number" name="qty" id="qty"
                    min="1" max="{{ $availableCount }}"
                    value="{{ old('qty', 10) }}"
                    class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm @error('qty') border-red-500 @enderror"
                    required>
                <p class="mt-1 text-xs text-stone-400">Tersedia: {{ $availableCount }}</p>
                @error('qty')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="rounded-full bg-emerald-700 px-7 py-3 text-sm font-semibold text-white hover:bg-emerald-800 transition">
                    Assign Voucher
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
