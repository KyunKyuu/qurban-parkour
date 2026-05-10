@extends('layouts.admin')

@section('title', 'Laporan Keuangan')

@section('content')
<div class="space-y-6">

    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-[2rem] bg-[#1a3628] px-7 py-8 text-[#f0ebe0] shadow-[0_24px_64px_rgba(26,54,40,0.28)]">
        <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full bg-white/[0.03]"></div>
        <div class="pointer-events-none absolute -left-8 bottom-0 h-40 w-40 rounded-full bg-white/[0.025]"></div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-[#a8c5b0]">Admin · Keuangan</p>
        <h2 class="display-font mt-3 text-4xl leading-tight">Laporan Keuangan</h2>
        <p class="mt-2 text-sm leading-6 text-[#a8c5b0]">
            Ringkasan dana masuk per kategori — <span class="font-semibold text-[#f0ebe0]">kotor</span> (semua submit) vs
            <span class="font-semibold text-[#e8a23e]">bersih</span> (sudah diverifikasi admin).
        </p>
    </section>

    {{-- KPI Overview --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        {{-- Kotor --}}
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Dana Masuk (Kotor)</p>
            <p class="mt-3 text-2xl font-extrabold text-[#1a3628]">Rp {{ number_format($overall->total_amount, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-stone-400">{{ number_format($overall->total_count) }} kontribusi</p>
        </div>

        {{-- Bersih --}}
        <div class="rounded-[1.6rem] border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-emerald-600">Dana Terverifikasi (Bersih)</p>
            <p class="mt-3 text-2xl font-extrabold text-emerald-800">Rp {{ number_format($overall->verified_amount, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-emerald-600">{{ number_format($overall->verified_count) }} kontribusi verified</p>
        </div>

        {{-- Pending --}}
        <div class="rounded-[1.6rem] border border-amber-200 bg-amber-50 p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-amber-700">Menunggu Verifikasi</p>
            <p class="mt-3 text-2xl font-extrabold text-amber-900">Rp {{ number_format($overall->pending_amount, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-amber-700">{{ number_format($overall->pending_count) }} kontribusi pending</p>
        </div>

        {{-- Anomali --}}
        <div class="rounded-[1.6rem] border border-red-200 bg-red-50 p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-red-600">Anomali</p>
            <p class="mt-3 text-2xl font-extrabold text-red-800">Rp {{ number_format($overall->anomaly_amount, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-red-600">{{ number_format($overall->anomaly_count) }} kontribusi anomali</p>
        </div>
    </section>

    {{-- Realisasi Bar --}}
    @if($overall->total_amount > 0)
    <section class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-semibold text-[#1a3628]">Realisasi Verifikasi</p>
            @php $pct = min(100, round($overall->verified_amount / $overall->total_amount * 100)); @endphp
            <span class="text-sm font-bold text-emerald-700">{{ $pct }}% terverifikasi</span>
        </div>
        <div class="h-3 w-full rounded-full bg-[#f0ebe0] overflow-hidden">
            <div class="h-full rounded-full bg-emerald-600 transition-all" style="width: {{ $pct }}%"></div>
        </div>
        <div class="mt-2 flex justify-between text-xs text-stone-400">
            <span>Rp 0</span>
            <span>Rp {{ number_format($overall->total_amount, 0, ',', '.') }}</span>
        </div>
    </section>
    @endif

    {{-- Per-Category Breakdown --}}
    <section class="rounded-[1.8rem] border border-[#e5e0d4] bg-white shadow-sm overflow-hidden">
        <div class="px-6 pt-6 pb-4">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Per Kategori</p>
            <h3 class="mt-1.5 text-lg font-bold text-[#1a3628]">Rincian dana masuk &amp; terverifikasi per kategori</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-[#f5f2ea]">
                    <tr class="text-left">
                        <th class="px-6 py-3 text-[11px] font-semibold uppercase tracking-[0.22em] text-stone-500">Kategori</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.22em] text-stone-500">Peserta</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.22em] text-stone-500">Dana Masuk (Kotor)</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-600">Terverifikasi (Bersih)</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.22em] text-amber-600">Menunggu</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.22em] text-red-500">Anomali</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-[0.22em] text-stone-500">% Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#f0ebe0]">
                    @foreach($categoryRows as $row)
                    @php
                        $rowPct = $row->total_amount > 0 ? round($row->verified_amount / $row->total_amount * 100) : 0;
                    @endphp
                    <tr class="hover:bg-[#faf8f4] transition">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-[#1a3628]">{{ $row->label }}</p>
                        </td>
                        <td class="px-6 py-4 text-right text-stone-600">
                            {{ number_format($row->total_count) }}
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-[#1a3628]">
                            Rp {{ number_format($row->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right font-semibold text-emerald-700">
                            Rp {{ number_format($row->verified_amount, 0, ',', '.') }}
                            <p class="text-[11px] font-normal text-emerald-500">{{ number_format($row->verified_count) }} peserta</p>
                        </td>
                        <td class="px-6 py-4 text-right text-amber-700">
                            @if($row->pending_count > 0)
                                Rp {{ number_format($row->pending_amount, 0, ',', '.') }}
                                <p class="text-[11px] text-amber-500">{{ number_format($row->pending_count) }} peserta</p>
                            @else
                                <span class="text-stone-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-red-600">
                            @if($row->anomaly_count > 0)
                                Rp {{ number_format($row->anomaly_amount, 0, ',', '.') }}
                                <p class="text-[11px] text-red-400">{{ number_format($row->anomaly_count) }} peserta</p>
                            @else
                                <span class="text-stone-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $rowPct >= 80 ? 'bg-emerald-100 text-emerald-700' : ($rowPct >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ $rowPct }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-[#1a3628]/20 bg-[#f5f2ea]">
                    <tr>
                        <td class="px-6 py-4 text-[11px] font-bold uppercase tracking-[0.22em] text-[#1a3628]">TOTAL</td>
                        <td class="px-6 py-4 text-right font-bold text-[#1a3628]">{{ number_format($overall->total_count) }}</td>
                        <td class="px-6 py-4 text-right font-bold text-[#1a3628]">Rp {{ number_format($overall->total_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-bold text-emerald-700">Rp {{ number_format($overall->verified_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-bold text-amber-700">Rp {{ number_format($overall->pending_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right font-bold text-red-600">Rp {{ number_format($overall->anomaly_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right">
                            @php $totalPct = $overall->total_amount > 0 ? round($overall->verified_amount / $overall->total_amount * 100) : 0; @endphp
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold {{ $totalPct >= 80 ? 'bg-emerald-100 text-emerald-700' : ($totalPct >= 50 ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ $totalPct }}%
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    {{-- Payment Method Breakdown --}}
    <section class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Metode Pembayaran</p>
        <h3 class="mt-1.5 text-lg font-bold text-[#1a3628]">Dana masuk per metode bayar</h3>

        <div class="mt-5 grid gap-4 md:grid-cols-{{ $paymentRows->count() > 1 ? '2' : '1' }}">
            @foreach($paymentRows as $row)
            @php
                $methodLabel = match(strtolower($row->method)) {
                    'transfer' => 'Transfer Bank',
                    'cash'     => 'Cash / Tunai',
                    default    => ucfirst($row->method),
                };
                $methodIcon = strtolower($row->method) === 'transfer' ? 'TR' : 'CS';
                $pctVerified = $row->total_amount > 0 ? round($row->verified_amount / $row->total_amount * 100) : 0;
            @endphp
            <div class="rounded-[1.4rem] border border-[#e5e0d4] p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[#f5f2ea] text-xs font-bold text-[#1a3628]">{{ $methodIcon }}</span>
                        <p class="mt-3 font-bold text-[#1a3628]">{{ $methodLabel }}</p>
                        <p class="text-xs text-stone-400">{{ number_format($row->total_count) }} kontribusi</p>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">{{ $pctVerified }}% bersih</span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-xl bg-[#f5f2ea] p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-stone-400">Masuk (Kotor)</p>
                        <p class="mt-1.5 text-sm font-bold text-[#1a3628]">Rp {{ number_format($row->total_amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-emerald-600">Terverifikasi</p>
                        <p class="mt-1.5 text-sm font-bold text-emerald-800">Rp {{ number_format($row->verified_amount, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-3 h-1.5 w-full rounded-full bg-[#f0ebe0] overflow-hidden">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $pctVerified }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </section>

</div>
@endsection
