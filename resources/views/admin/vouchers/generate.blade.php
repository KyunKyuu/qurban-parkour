@extends('layouts.admin')

@section('title', 'Generate Voucher')

@section('content')
<div class="rounded-[1.8rem] bg-white border border-emerald-100 p-6 shadow-sm max-w-xl">
    <h2 class="text-xl font-bold text-emerald-950 mb-6">Generate Voucher Baru</h2>

    <form method="POST" action="{{ route('admin.vouchers.generate') }}" class="space-y-5">
        @csrf

        <div>
            <label for="count" class="block text-sm font-medium text-emerald-800">Jumlah Voucher</label>
            <input
                type="number"
                name="count"
                id="count"
                min="1"
                max="1000"
                value="{{ old('count', 10) }}"
                class="mt-1 block w-full rounded-xl border border-emerald-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 @error('count') border-red-400 @enderror"
                required
            >
            @error('count')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-emerald-500">Maksimal 1.000 voucher per batch</p>
        </div>

        <div>
            <label for="batch_name" class="block text-sm font-medium text-emerald-800">Nama Batch (Opsional)</label>
            <input
                type="text"
                name="batch_name"
                id="batch_name"
                value="{{ old('batch_name') }}"
                placeholder="cth. Qurban Parkour Wave 1"
                class="mt-1 block w-full rounded-xl border border-emerald-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 @error('batch_name') border-red-400 @enderror"
            >
            @error('batch_name')
                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-emerald-500">Kosong = nama otomatis berdasarkan waktu generate</p>
        </div>

        <div class="flex justify-end pt-2">
            <button
                type="submit"
                class="rounded-xl bg-emerald-700 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500"
            >
                Generate Voucher
            </button>
        </div>
    </form>
</div>
@endsection
