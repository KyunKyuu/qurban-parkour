@extends('layouts.admin')

@section('title', 'Assign Vouchers')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Assign Vouchers ke PJ</h2>

    <!-- Available Stock Info -->
    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
        <p class="text-sm font-medium text-blue-900">
            Voucher Tersedia (belum di-assign): <span class="text-lg font-bold">{{ $availableCount }}</span>
        </p>
    </div>

    <form method="POST" action="{{ route('admin.vouchers.assign') }}" class="space-y-6">
        @csrf

        <!-- PJ Selection -->
        <div>
            <label for="pic_id" class="block text-sm font-medium text-gray-700">Pilih PJ</label>
            <select
                name="pic_id"
                id="pic_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('pic_id') border-red-500 @enderror"
                required
            >
                <option value="">-- Pilih PJ --</option>
                @foreach($pics as $pic)
                    <option value="{{ $pic->id }}" {{ old('pic_id') == $pic->id ? 'selected' : '' }}>
                        {{ $pic->name }} @if($pic->code)({{ $pic->code }})@endif
                    </option>
                @endforeach
            </select>
            @error('pic_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Community Selection (muncul dinamis setelah PJ dipilih) -->
        <div id="community-wrapper" class="hidden">
            <label for="community_id" class="block text-sm font-medium text-gray-700">
                Pilih Komunitas
            </label>
            <select
                name="community_id"
                id="community_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('community_id') border-red-500 @enderror"
            >
                <option value="">-- Tanpa Komunitas (langsung ke PJ) --</option>
            </select>
            <p class="mt-1 text-sm text-gray-500">
                Kosongkan jika voucher ini langsung dipegang PJ tanpa komunitas tertentu.
            </p>
            @error('community_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Batch Selection (Optional) -->
        <div>
            <label for="batch_id" class="block text-sm font-medium text-gray-700">Filter Batch (Opsional)</label>
            <select
                name="batch_id"
                id="batch_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
            >
                <option value="">-- Semua Batch --</option>
                @foreach($batches as $batch)
                    <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}>
                        {{ $batch->name }} ({{ $batch->generated_count }} voucher)
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-sm text-gray-500">Kosongkan untuk assign dari semua batch</p>
        </div>

        <!-- Quantity Input -->
        <div>
            <label for="qty" class="block text-sm font-medium text-gray-700">Jumlah Voucher</label>
            <input
                type="number"
                name="qty"
                id="qty"
                min="1"
                max="{{ $availableCount }}"
                value="{{ old('qty', 10) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('qty') border-red-500 @enderror"
                required
            >
            @error('qty')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">Tersedia: {{ $availableCount }}</p>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end space-x-3">
            <button
                type="submit"
                class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                Assign Voucher
            </button>
        </div>
    </form>
</div>

<script>
    const communitiesByPic = @json($communitiesByPic);
    const picSelect        = document.getElementById('pic_id');
    const communityWrapper = document.getElementById('community-wrapper');
    const communitySelect  = document.getElementById('community_id');
    const oldCommunityId   = "{{ old('community_id') }}";

    function updateCommunities() {
        const picId = picSelect.value;
        const communities = communitiesByPic[picId] || [];

        // Reset options
        communitySelect.innerHTML = '<option value="">-- Tanpa Komunitas (langsung ke PJ) --</option>';

        if (communities.length > 0) {
            communities.forEach(function (c) {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                if (String(c.id) === oldCommunityId) opt.selected = true;
                communitySelect.appendChild(opt);
            });
            communityWrapper.classList.remove('hidden');
        } else {
            // PJ tanpa komunitas (default PJ) – sembunyikan dropdown
            communityWrapper.classList.add('hidden');
            communitySelect.value = '';
        }
    }

    picSelect.addEventListener('change', updateCommunities);

    // Trigger on page load jika ada old value (setelah validasi gagal)
    if (picSelect.value) updateCommunities();
</script>
@endsection
