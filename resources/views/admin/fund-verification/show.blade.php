@extends('layouts.admin')

@section('title', 'Detail Verifikasi Dana - ' . \Carbon\Carbon::parse($date)->translatedFormat('d F Y'))

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-emerald-950">Detail Verifikasi Dana</h1>
            <p class="text-sm text-emerald-500">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }} (Cutoff 20:00)</p>
        </div>
        <a href="{{ route('admin.fund-verification.index') }}" class="text-emerald-600 hover:text-emerald-900 text-sm">
            &larr; Kembali
        </a>
    </div>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-[1.4rem] bg-white border border-emerald-100 p-4 shadow-sm">
            <p class="text-sm text-emerald-600">Total Voucher</p>
            <p class="text-2xl font-extrabold text-emerald-950">{{ number_format($stats['total_vouchers']) }}</p>
        </div>
        <div class="rounded-[1.4rem] bg-white border border-emerald-100 p-4 shadow-sm">
            <p class="text-sm text-emerald-600">Total Dana</p>
            <p class="text-2xl font-extrabold text-emerald-950">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-[1.4rem] bg-white border border-orange-100 p-4 shadow-sm">
            <p class="text-sm text-orange-600">Pending</p>
            <p class="text-2xl font-extrabold text-orange-600">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="rounded-[1.4rem] bg-white border border-red-100 p-4 shadow-sm">
            <p class="text-sm text-red-500">Anomali</p>
            <p class="text-2xl font-extrabold text-red-600">{{ number_format($stats['anomaly']) }}</p>
        </div>
    </div>

    {{-- Verify all action --}}
    @if($stats['pending'] > 0)
    <div class="rounded-[1.4rem] bg-orange-50 border border-orange-200 p-4 flex justify-between items-center gap-4">
        <p class="text-orange-800 text-sm font-medium">
            Terdapat {{ $stats['pending'] }} kontribusi yang belum diverifikasi. Pastikan data fisik sudah sesuai.
        </p>
        <form action="{{ route('admin.fund-verification.verify-day', $date) }}" method="POST"
              onsubmit="return confirm('Data fisik sudah sesuai? Semua voucher pending akan diverifikasi.')">
            @csrf
            <button type="submit" class="whitespace-nowrap rounded-xl bg-emerald-700 text-white px-4 py-2 text-sm font-medium hover:bg-emerald-800">
                Verifikasi {{ $stats['pending'] }} Kontribusi
            </button>
        </form>
    </div>
    @endif

    {{-- Table --}}
    <div class="rounded-[1.8rem] bg-white border border-emerald-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-emerald-100 text-sm whitespace-nowrap">
                <thead class="bg-emerald-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Kode Voucher</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Peserta</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Kategori</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Komunitas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">PJ</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-emerald-600">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Metode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Bukti TF</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-50">
                    @foreach($claims as $claim)
                        <tr class="{{ $claim->verification_status == 'ANOMALY' ? 'bg-red-50' : 'hover:bg-emerald-50/40' }}">
                            <td class="px-4 py-4 text-emerald-500 text-xs">
                                {{ $claim->created_at->format('H:i:s') }}
                            </td>
                            <td class="px-4 py-4 font-mono text-sm font-semibold text-emerald-900">
                                {{ $claim->initialVoucher->code }}
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-emerald-950">{{ $claim->name }}</p>
                                <p class="text-xs text-emerald-400">{{ $claim->phone }}</p>
                            </td>
                            <td class="px-4 py-4">
                                @php
                                    $catColor = match($claim->category_type) {
                                        'DOMBA'    => 'bg-amber-100 text-amber-800',
                                        'SAPI'     => 'bg-emerald-100 text-emerald-800',
                                        'SAPI_1_7' => 'bg-sky-100 text-sky-800',
                                        'PATUNGAN' => 'bg-rose-100 text-rose-800',
                                        default    => 'bg-stone-100 text-stone-600',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $catColor }}">
                                    {{ $claim->category_label ?: ($claim->category_type ?: 'Legacy') }}
                                </span>
                                @if($claim->patungan_target)
                                    <p class="text-xs text-emerald-400 mt-0.5">→ {{ $claim->patungan_target }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if($claim->initialVoucher->community)
                                    <span class="text-sm text-emerald-700">{{ $claim->initialVoucher->community->name }}</span>
                                @else
                                    <span class="text-xs text-emerald-300 italic">Langsung</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-emerald-600 text-sm">
                                {{ $claim->initialVoucher->pic->name ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-right font-bold text-emerald-950">
                                Rp {{ number_format($claim->total_donation_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4">
                                @if($claim->payment_method === 'transfer')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-800">
                                        Transfer
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                        Cash
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if($claim->transfer_proof_path)
                                    <button onclick="openProof('{{ Storage::url($claim->transfer_proof_path) }}')"
                                            class="block rounded-lg overflow-hidden border border-emerald-200 hover:border-orange-400 transition">
                                        <img src="{{ Storage::url($claim->transfer_proof_path) }}"
                                             alt="Bukti TF"
                                             class="h-10 w-14 object-cover">
                                    </button>
                                @else
                                    <span class="text-xs text-emerald-300">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if($claim->verification_status == 'VERIFIED')
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        VERIFIED
                                    </span>
                                @elseif($claim->verification_status == 'ANOMALY')
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-red-100 text-red-800">
                                        ANOMALY
                                    </span>
                                    @if($claim->verification_note)
                                        <p class="text-xs text-red-500 mt-0.5 max-w-[120px] truncate" title="{{ $claim->verification_note }}">
                                            {{ $claim->verification_note }}
                                        </p>
                                    @endif
                                @else
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-orange-100 text-orange-700">
                                        PENDING
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if($claim->verification_status != 'ANOMALY')
                                    <button onclick="openAnomalyModal('{{ $claim->id }}', '{{ $claim->initialVoucher->code }}')"
                                            class="text-sm text-red-500 hover:text-red-700 font-medium">
                                        Tandai Anomali
                                    </button>
                                @else
                                    <span class="text-xs text-emerald-300">Marked</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Anomaly Modal --}}
<div id="anomalyModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-[1.6rem] p-6 w-96 shadow-2xl">
        <h3 class="text-lg font-bold text-emerald-950">Catat Anomali</h3>
        <p class="mt-1 text-sm text-emerald-500">Voucher: <span id="modalVoucherCode" class="font-mono font-bold text-emerald-900"></span></p>
        <form id="anomalyForm" method="POST" class="mt-5 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-emerald-800 mb-1">Catatan anomali</label>
                <textarea name="note" rows="3" required placeholder="Contoh: Jumlah uang fisik kurang Rp 50.000"
                          class="w-full rounded-xl border border-emerald-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400"></textarea>
            </div>
            <button type="submit" class="w-full rounded-xl bg-red-600 text-white py-2.5 text-sm font-semibold hover:bg-red-700">
                Simpan Anomali
            </button>
            <button type="button" onclick="closeAnomalyModal()" class="w-full rounded-xl bg-emerald-50 text-emerald-700 py-2.5 text-sm font-medium hover:bg-emerald-100">
                Batal
            </button>
        </form>
    </div>
</div>

{{-- Proof Zoom Modal --}}
<div id="proofModal" class="fixed inset-0 bg-black/80 hidden z-50 flex items-center justify-center p-4" onclick="closeProof()">
    <img id="proofImage" src="" alt="Bukti Transfer" class="max-h-[90vh] max-w-[90vw] rounded-2xl shadow-2xl object-contain">
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
    function openProof(url) {
        document.getElementById('proofImage').src = url;
        document.getElementById('proofModal').classList.remove('hidden');
    }
    function closeProof() {
        document.getElementById('proofModal').classList.add('hidden');
    }
</script>
@endsection
