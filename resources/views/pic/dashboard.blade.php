@extends('layouts.pic')

@section('title', 'Dashboard PIC')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Assigned -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Voucher Assigned</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($stats['assigned']) }}</p>
                </div>
            </div>
        </div>

        <!-- Claimed -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Voucher Claimed</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($stats['claimed']) }}</p>
                </div>
            </div>
        </div>

        <!-- Redeemed -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Voucher Redeemed</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($stats['redeemed']) }}</p>
                </div>
            </div>
        </div>

        <!-- Commission -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-teal-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-teal-100 text-teal-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Keuntungan</p>
                    <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($stats['commission'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables using Tabs (Simplified visual separation) -->
    
    <!-- Assigned Vouchers -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-medium text-gray-900">Daftar Voucher Assigned</h3>
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-2">
                    <label for="assigned_per_page" class="text-sm text-gray-600">Tampilkan:</label>
                    <select id="assigned_per_page" onchange="window.location.href = '{{ route('pic.dashboard') }}?assigned_per_page=' + this.value + '&claimed_page={{ $claimedVouchers->currentPage() }}&redeemed_page={{ $redeemedVouchers->currentPage() }}&claimed_per_page={{ request('claimed_per_page', 10) }}&redeemed_per_page={{ request('redeemed_per_page', 10) }}'" class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 p-1 border">
                        <option value="10" {{ request('assigned_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('assigned_per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('assigned_per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('assigned_per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div>
                    <a href="{{ route('pic.vouchers.export') }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm inline-flex items-center transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export PDF Voucher
                    </a>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Voucher</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Donatur</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Zakat Fitrah</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Zakat Mal</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Infaq</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sodaqoh</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Donasi</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Komisi</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($assignedVouchers as $voucher)
                    <tr>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ ($assignedVouchers->currentPage() - 1) * $assignedVouchers->perPage() + $loop->iteration }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $voucher->code }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $voucher->batch->name ?? '-' }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $voucher->claim?->name ?? '-' }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->zakat_fitrah_amount > 0)Rp {{ number_format($voucher->claim->zakat_fitrah_amount, 0, ',', '.') }}@else -@endif</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->zakat_mal_amount > 0)Rp {{ number_format($voucher->claim->zakat_mal_amount, 0, ',', '.') }}@else -@endif</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->infaq_amount > 0)Rp {{ number_format($voucher->claim->infaq_amount, 0, ',', '.') }}@else -@endif</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->sodaqoh_amount > 0)Rp {{ number_format($voucher->claim->sodaqoh_amount, 0, ',', '.') }}@else -@endif</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 font-medium text-right">Rp {{ number_format((float)(($voucher->claim?->zakat_fitrah_amount ?? 0) + ($voucher->claim?->zakat_mal_amount ?? 0) + ($voucher->claim?->infaq_amount ?? 0) + ($voucher->claim?->sodaqoh_amount ?? 0)), 0, ',', '.') }}</td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-right">Rp {{ number_format($voucher->commission_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                Assigned
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-4 py-4 text-center text-sm text-gray-500">Belum ada voucher yang di-assign.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $assignedVouchers->appends(['claimed_page' => $claimedVouchers->currentPage(), 'redeemed_page' => $redeemedVouchers->currentPage(), 'assigned_per_page' => request('assigned_per_page', 10), 'claimed_per_page' => request('claimed_per_page', 10), 'redeemed_per_page' => request('redeemed_per_page', 10)])->links() }}
        </div>
    </div>

    <!-- Claimed Vouchers -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-medium text-gray-900">Daftar Voucher Claimed (Belum Redeem)</h3>
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-2">
                    <label for="claimed_per_page" class="text-sm text-gray-600">Tampilkan:</label>
                    <select id="claimed_per_page" onchange="window.location.href = '{{ route('pic.dashboard') }}?claimed_per_page=' + this.value + '&assigned_page={{ $assignedVouchers->currentPage() }}&redeemed_page={{ $redeemedVouchers->currentPage() }}&assigned_per_page={{ request('assigned_per_page', 10) }}&redeemed_per_page={{ request('redeemed_per_page', 10) }}'" class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-teal-500 focus:border-teal-500 p-1 border">
                        <option value="10" {{ request('claimed_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('claimed_per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('claimed_per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('claimed_per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <button onclick="document.getElementById('exportModal').classList.remove('hidden')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded text-sm inline-flex items-center transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Export Data Excel
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Voucher</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Donatur</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ZF</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ZM</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">I</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">S</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Claim</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Voucher</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Dana</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($claimedVouchers as $voucher)
                    <tr>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ ($claimedVouchers->currentPage() - 1) * $claimedVouchers->perPage() + $loop->iteration }}</td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $voucher->code }}</td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $voucher->claim?->name ?? '-' }}<br>
                            <span class="text-xs text-gray-400">{{ $voucher->claim?->phone ?? '' }}</span>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->zakat_fitrah_amount > 0){{ number_format($voucher->claim->zakat_fitrah_amount, 0, ',', '.') }}@else -@endif</td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->zakat_mal_amount > 0){{ number_format($voucher->claim->zakat_mal_amount, 0, ',', '.') }}@else -@endif</td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->infaq_amount > 0){{ number_format($voucher->claim->infaq_amount, 0, ',', '.') }}@else -@endif</td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->sodaqoh_amount > 0){{ number_format($voucher->claim->sodaqoh_amount, 0, ',', '.') }}@else -@endif</td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-green-600 font-medium text-right">{{ number_format((float)(($voucher->claim?->zakat_fitrah_amount ?? 0) + ($voucher->claim?->zakat_mal_amount ?? 0) + ($voucher->claim?->infaq_amount ?? 0) + ($voucher->claim?->sodaqoh_amount ?? 0)), 0, ',', '.') }}</td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $voucher->claimed_at ? $voucher->claimed_at->format('d M Y') : '-' }}</td>
                        <td class="px-3 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                Claimed
                            </span>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap">
                            @if($voucher->claim?->verification_status == 'VERIFIED')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Verified
                                </span>
                            @elseif($voucher->claim?->verification_status == 'ANOMALY')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800" title="{{ $voucher->claim?->verification_note }}">
                                    Anomali
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Pending
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-3 py-4 text-center text-sm text-gray-500">Belum ada voucher yang di-claim.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $claimedVouchers->appends(['assigned_page' => $assignedVouchers->currentPage(), 'redeemed_page' => $redeemedVouchers->currentPage(), 'claimed_per_page' => request('claimed_per_page', 10)])->links() }}
        </div>
    </div>

    <!-- Redeemed Vouchers -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <h3 class="text-lg font-medium text-gray-900">Daftar Voucher Redeemed (Komisi Cair)</h3>
            <div class="flex items-center space-x-2">
                <label for="redeemed_per_page" class="text-sm text-gray-600">Tampilkan:</label>
                <select id="redeemed_per_page" onchange="window.location.href = '{{ route('pic.dashboard') }}?redeemed_per_page=' + this.value + '&assigned_page={{ $assignedVouchers->currentPage() }}&claimed_page={{ $claimedVouchers->currentPage() }}&assigned_per_page={{ request('assigned_per_page', 10) }}&claimed_per_page={{ request('claimed_per_page', 10) }}'" class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 p-1 border">
                    <option value="10" {{ request('redeemed_per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('redeemed_per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('redeemed_per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('redeemed_per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span class="text-sm text-gray-600">data</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Voucher</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merchant</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Donatur</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ZF</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">ZM</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">I</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">S</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Komisi</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Redeem</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php $redeemedCount = 0; @endphp
                    @forelse($redeemedVouchers as $voucher)
                        @foreach($voucher->merchantVouchers as $mv)
                            @if($mv->status === 'REDEEMED')
                            @php $redeemedCount++; @endphp
                            <tr>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ ($redeemedVouchers->currentPage() - 1) * $redeemedVouchers->perPage() + $redeemedCount }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $voucher->code }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mv->merchant->name ?? '-' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $voucher->claim?->name ?? '-' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->zakat_fitrah_amount > 0){{ number_format($voucher->claim->zakat_fitrah_amount, 0, ',', '.') }}@else -@endif</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->zakat_mal_amount > 0){{ number_format($voucher->claim->zakat_mal_amount, 0, ',', '.') }}@else -@endif</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->infaq_amount > 0){{ number_format($voucher->claim->infaq_amount, 0, ',', '.') }}@else -@endif</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-right">@if($voucher->claim && $voucher->claim->sodaqoh_amount > 0){{ number_format($voucher->claim->sodaqoh_amount, 0, ',', '.') }}@else -@endif</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900 font-medium text-right">{{ number_format((float)(($voucher->claim?->zakat_fitrah_amount ?? 0) + ($voucher->claim?->zakat_mal_amount ?? 0) + ($voucher->claim?->infaq_amount ?? 0) + ($voucher->claim?->sodaqoh_amount ?? 0)), 0, ',', '.') }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm font-bold text-green-600 text-right">{{ number_format($voucher->commission_amount, 0, ',', '.') }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mv->redeemed_at ? $mv->redeemed_at->format('d M Y') : '-' }}</td>
                            </tr>
                            @endif
                        @endforeach
                    @empty
                    <tr>
                        <td colspan="11" class="px-3 py-4 text-center text-sm text-gray-500">Belum ada voucher yang di-redeem.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $redeemedVouchers->appends(['assigned_page' => $assignedVouchers->currentPage(), 'claimed_page' => $claimedVouchers->currentPage(), 'redeemed_per_page' => request('redeemed_per_page', 10), 'assigned_per_page' => request('assigned_per_page', 10), 'claimed_per_page' => request('claimed_per_page', 10)])->links() }}
        </div>
    </div>


</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('exportModal').classList.add('hidden')"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('pic.data.export') }}" method="GET">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Export Data Pendataan
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tipe Data</label>
                                    <select name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm p-2 border">
                                        <option value="claimed">Data Claimed (Default)</option>
                                        <option value="redeem">Data Redeem</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Dari Tanggal</label>
                                        <input type="date" name="date_from" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm p-2 border">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Sampai Tanggal</label>
                                        <input type="date" name="date_to" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm p-2 border">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 italic">* Kosongkan tanggal untuk export semua data (all time).</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Download Excel (CSV)
                    </button>
                    <button type="button" onclick="document.getElementById('exportModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
