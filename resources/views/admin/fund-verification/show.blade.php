@extends('layouts.admin')

@section('title', 'Detail Verifikasi Dana - ' . \Carbon\Carbon::parse($date)->translatedFormat('d F Y'))

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Verifikasi Dana</h1>
            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }} (Cutoff 20:00)</p>
        </div>
        <a href="{{ route('admin.fund-verification.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Kembali
        </a>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
            <div class="text-sm text-gray-500">Total Voucher</div>
            <div class="text-2xl font-bold">{{ number_format($stats['total_vouchers']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-teal-500">
            <div class="text-sm text-gray-500">Total Dana</div>
            <div class="text-2xl font-bold">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
            <div class="text-sm text-gray-500">Pending</div>
            <div class="text-2xl font-bold">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-500">
            <div class="text-sm text-gray-500">Anomali</div>
            <div class="text-2xl font-bold">{{ number_format($stats['anomaly']) }}</div>
        </div>
    </div>

    <!-- Actions -->
    @if($stats['pending'] > 0)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="text-yellow-800 font-medium">Terdapat {{ $stats['pending'] }} voucher yang belum diverifikasi. Pastikan data fisik sudah sesuai.</span>
        </div>
        <form action="{{ route('admin.fund-verification.verify-day', $date) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin data fisik sudah sesuai? Aksi ini akan memverifikasi semua voucher pending hari ini.')">
            @csrf
            <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded hover:bg-teal-700 font-medium">
                Verifikasi {{ $stats['pending'] }} Voucher
            </button>
        </form>
    </div>
    @endif

    <!-- Voucher List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto overflow-y-hidden">
            <table class="min-w-full divide-y divide-gray-200 whitespace-nowrap">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Claim</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Voucher</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donatur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC / Batch</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($claims as $claim)
                    <tr class="{{ $claim->verification_status == 'ANOMALY' ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $claim->created_at->format('H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $claim->initialVoucher->code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $claim->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">
                            Rp {{ number_format($claim->total_donation_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $claim->initialVoucher->pic->name ?? '-' }} <br>
                            <span class="text-xs">{{ $claim->initialVoucher->batch->name ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($claim->verification_status == 'VERIFIED')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    VERIFIED
                                </span>
                            @elseif($claim->verification_status == 'ANOMALY')
                                <div class="flex flex-col">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 w-fit">
                                        ANOMALY
                                    </span>
                                    <span class="text-xs text-red-600 mt-1 max-w-[150px] truncate" title="{{ $claim->verification_note }}">
                                        {{ $claim->verification_note }}
                                    </span>
                                </div>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    PENDING
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($claim->verification_status != 'ANOMALY')
                                <button onclick="openAnomalyModal('{{ $claim->id }}', '{{ $claim->initialVoucher->code }}')" class="text-red-600 hover:text-red-900">
                                    Tandai Anomali
                                </button>
                            @else
                                <span class="text-gray-400">Marked</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Anomaly Modal -->
<div id="anomalyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full" style="z-index: 50;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Catat Anomali</h3>
            <div class="mt-2 text-sm text-gray-500">
                Voucher: <span id="modalVoucherCode" class="font-bold"></span>
            </div>
            <form id="anomalyForm" method="POST" class="mt-4">
                @csrf
                <div class="text-left">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Catatan:</label>
                    <textarea name="note" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="3" required placeholder="Contoh: Jumlah uang fisik kurang"></textarea>
                </div>
                <div class="items-center px-4 py-3">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
                        Simpan Anomali
                    </button>
                    <button type="button" onclick="closeAnomalyModal()" class="mt-3 px-4 py-2 bg-gray-100 text-gray-700 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openAnomalyModal(claimId, voucherCode) {
        document.getElementById('modalVoucherCode').innerText = voucherCode;
        document.getElementById('anomalyForm').action = "/admin/fund-verification/" + claimId + "/anomaly";
        document.getElementById('anomalyModal').classList.remove('hidden');
    }

    function closeAnomalyModal() {
        document.getElementById('anomalyModal').classList.add('hidden');
    }
</script>
@endsection
