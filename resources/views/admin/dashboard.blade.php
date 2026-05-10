@extends('layouts.admin')

@section('title', 'Dashboard Kurban')

@section('content')
<div class="space-y-6">

    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-[2rem] bg-[#1a3628] px-7 py-8 text-[#f0ebe0] shadow-[0_24px_64px_rgba(26,54,40,0.28)]">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/[0.03]"></div>
        <div class="pointer-events-none absolute -bottom-10 right-24 h-40 w-40 rounded-full bg-white/[0.03]"></div>
        <p class="text-[11px] font-semibold uppercase tracking-[0.32em] text-[#a8c5b0]">Phase 1 Internal Focus</p>
        <div class="mt-4 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="display-font text-4xl leading-tight tracking-tight text-[#f0ebe0]">{{ config('qurban.campaign_name') }}</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-[#a8c5b0]">{{ config('qurban.campaign_tagline') }}</p>
            </div>
            <div class="flex flex-col gap-3 lg:items-end lg:shrink-0">
                <div class="rounded-2xl border border-white/10 bg-white/5 px-5 py-3.5 text-sm backdrop-blur-sm">
                    <p class="text-[11px] uppercase tracking-[0.24em] text-[#a8c5b0]">Penutupan patungan</p>
                    <p class="mt-1 font-bold text-[#f0ebe0]">{{ config('qurban.closing_label') }}</p>
                </div>
                <a href="{{ route('admin.settings.qurban.edit') }}" class="inline-flex items-center justify-center rounded-full bg-[#e8a23e] px-6 py-3 text-sm font-semibold text-[#1a3628] shadow-[0_4px_16px_rgba(232,162,62,0.35)] transition hover:bg-[#d4912e]">
                    Atur Pricing & Campaign
                </a>
            </div>
        </div>
    </section>

    {{-- KPI Cards --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Total Terkumpul</p>
            <p class="mt-3 text-2xl font-extrabold leading-tight text-[#1a3628]">Rp {{ number_format($kpis['total_amount'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Total Peserta</p>
            <p class="mt-3 text-3xl font-extrabold leading-tight text-[#1a3628]">{{ number_format($kpis['total_participants']) }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Kontribusi Masuk</p>
            <p class="mt-3 text-3xl font-extrabold leading-tight text-[#1a3628]">{{ number_format($kpis['total_claims']) }}</p>
        </div>
        <div class="rounded-[1.6rem] border border-[#e5e0d4] bg-white p-5 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Sertifikat Generated</p>
            <p class="mt-3 text-3xl font-extrabold leading-tight text-[#1a3628]">{{ number_format($kpis['total_certificates']) }}</p>
        </div>
    </section>

    {{-- Chart + Kategori --}}
    <section class="grid gap-6 xl:grid-cols-[1.25fr_0.75fr]">
        <div class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Tren Harian</p>
                    <h3 class="mt-1.5 text-lg font-bold text-[#1a3628]">Nominal kontribusi 30 hari terakhir</h3>
                </div>
            </div>
            <div class="mt-6 h-72">
                <canvas id="contributionChart"></canvas>
            </div>
        </div>

        <div class="rounded-[1.8rem] border border-[#f0d9b0] bg-[#fdf6e9] p-6 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-amber-600">Pool Patungan</p>
            <p class="mt-1.5 text-sm text-stone-500">Dana terkumpul, alokasi hewan dikelola oleh admin</p>
            <div class="mt-6 grid grid-cols-2 gap-4">
                <div class="rounded-2xl bg-white/70 p-4">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-stone-400">Total Terkumpul</p>
                    <p class="mt-2 text-xl font-extrabold text-[#1a3628]">Rp {{ number_format($patunganPool['total_collected'], 0, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl bg-white/70 p-4">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-stone-400">Jumlah Peserta</p>
                    <p class="mt-2 text-xl font-extrabold text-[#1a3628]">{{ number_format($patunganPool['claim_count']) }}</p>
                </div>
            </div>
            @if($patunganPool['claim_count'] === 0)
            <p class="mt-5 text-center text-sm text-amber-700/60">Belum ada kontribusi patungan masuk.</p>
            @endif
        </div>
    </section>

    {{-- Activity Feed + Sertifikat --}}
    <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Activity Feed</p>
                    <h3 class="mt-1.5 text-lg font-bold text-[#1a3628]">Kontribusi terbaru</h3>
                </div>
                <a href="{{ route('admin.claims.index') }}" class="text-xs font-semibold text-[#e8a23e] hover:text-[#d4912e]">Lihat semua</a>
            </div>
            <div class="mt-5 divide-y divide-[#f0ebe0]">
                @forelse($recentClaims as $claim)
                <div class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
                    <div class="min-w-0">
                        <p class="font-semibold text-[#1a3628]">{{ $claim->name }}</p>
                        <p class="mt-0.5 text-xs text-stone-500">{{ $claim->display_category_label }} &middot; Rp {{ number_format($claim->total_donation_amount, 0, ',', '.') }}</p>
                        <p class="mt-0.5 text-[11px] text-stone-400">{{ $claim->pic?->name ?? $claim->initialVoucher?->pic?->name ?? 'Tanpa PIC' }}</p>
                    </div>
                    <span class="shrink-0 text-[11px] text-stone-400">{{ $claim->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <p class="py-6 text-center text-sm text-stone-400">Belum ada kontribusi tercatat.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Sertifikat</p>
                    <h3 class="mt-1.5 text-lg font-bold text-[#1a3628]">Generate terbaru</h3>
                </div>
            </div>
            <div class="mt-5 divide-y divide-[#f0ebe0]">
                @forelse($recentCertificates as $claim)
                <div class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
                    <div class="min-w-0">
                        <p class="font-semibold text-[#1a3628]">{{ $claim->name }}</p>
                        <p class="mt-0.5 text-xs text-stone-500">{{ $claim->display_category_label }}</p>
                        <p class="mt-0.5 text-[11px] text-stone-400">{{ optional($claim->certificate_generated_at)->format('d M Y H:i') }}</p>
                    </div>
                    <a href="{{ route('admin.claims.certificate', $claim->id) }}" class="shrink-0 rounded-full bg-[#1a3628] px-4 py-2 text-[11px] font-semibold text-[#e8a23e] transition hover:bg-[#0f2d1e]">Unduh</a>
                </div>
                @empty
                <p class="py-6 text-center text-sm text-stone-400">Belum ada sertifikat yang tergenerate.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- Top PIC + Patungan --}}
    <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Top PIC</p>
            <div class="mt-5 divide-y divide-[#f0ebe0]">
                @foreach($topPics as $i => $pic)
                <div class="flex items-center gap-4 py-4 first:pt-0 last:pb-0">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#f5f2ea] text-[11px] font-bold text-[#1a3628]">{{ $i + 1 }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-[#1a3628]">{{ $pic['name'] }}</p>
                        <p class="text-xs text-stone-400">{{ $pic['total_claims'] }} kontribusi</p>
                    </div>
                    <p class="shrink-0 text-sm font-bold text-[#1a3628]">Rp {{ number_format($pic['total_amount'], 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
            <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Ringkasan Kategori</p>
            <div class="mt-5 divide-y divide-[#f0ebe0]">
                @foreach($categoryStats as $category)
                <div class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0">
                    <div>
                        <p class="font-semibold text-[#1a3628]">{{ $category->label }}</p>
                        <p class="mt-0.5 text-xs text-stone-400">{{ number_format($category->total_claims) }} kontribusi</p>
                    </div>
                    <p class="shrink-0 text-sm font-bold text-[#1a3628]">Rp {{ number_format($category->total_amount, 0, ',', '.') }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Settings + Pricing --}}
    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Settings Aktif</p>
                    <h3 class="mt-1.5 text-lg font-bold text-[#1a3628]">Status kampanye yang sedang dipakai sistem</h3>
                </div>
                <a href="{{ route('admin.settings.qurban.edit') }}" class="text-xs font-semibold text-[#e8a23e] hover:text-[#d4912e]">Edit</a>
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-2">
                <div class="rounded-2xl bg-[#faf8f4] p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-stone-400">Status Kontribusi</p>
                    <p class="mt-2 text-base font-bold {{ !empty($settings['claim_open']) ? 'text-emerald-700' : 'text-red-600' }}">
                        {{ !empty($settings['claim_open']) ? 'DIBUKA' : 'DITUTUP' }}
                    </p>
                </div>
                <div class="rounded-2xl bg-[#faf8f4] p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-stone-400">Penutupan</p>
                    <p class="mt-2 text-sm font-bold text-[#1a3628]">{{ $settings['closing_label'] }}</p>
                </div>
                <div class="rounded-2xl bg-[#faf8f4] p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-stone-400">PIC Default</p>
                    <p class="mt-2 text-sm font-bold text-[#1a3628]">{{ $settings['default_pic_label'] }}</p>
                    <p class="mt-0.5 text-[11px] text-stone-400">{{ $settings['default_pic_email'] }}</p>
                </div>
                <div class="rounded-2xl bg-[#faf8f4] p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-stone-400">Target Patungan</p>
                    <p class="mt-2 text-sm font-bold text-[#1a3628]">{{ $patunganTargetLabels->isNotEmpty() ? $patunganTargetLabels->implode(', ') : '-' }}</p>
                </div>
                <div class="rounded-2xl bg-[#faf8f4] p-4 md:col-span-2">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-stone-400">Tujuan Transfer</p>
                    <p class="mt-2 text-sm font-bold text-[#1a3628]">{{ $settings['bank_account_label'] }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-[1.8rem] border border-[#e5e0d4] bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.26em] text-stone-400">Pricing Snapshot</p>
                    <h3 class="mt-1.5 text-lg font-bold text-[#1a3628]">Harga dan komisi kategori saat ini</h3>
                </div>
            </div>
            <div class="mt-5 divide-y divide-[#f0ebe0]">
                @foreach($pricingOptions as $category)
                <div class="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
                    <div class="min-w-0">
                        <p class="font-semibold text-[#1a3628]">{{ $category['label'] }}</p>
                        <p class="mt-0.5 text-xs leading-5 text-stone-400">{{ $category['description'] }}</p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-sm font-bold text-[#1a3628]">
                            {{ (float) ($category['price'] ?? 0) > 0 ? 'Rp ' . number_format($category['price'], 0, ',', '.') : 'Nominal bebas' }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-stone-400">
                            Komisi Rp {{ number_format($category['commission'] ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartCanvas = document.getElementById('contributionChart');

    if (chartCanvas) {
        new Chart(chartCanvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Nominal Kontribusi',
                        data: {!! json_encode($chartData) !!},
                        borderColor: '#1a3628',
                        backgroundColor: 'rgba(26, 54, 40, 0.06)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 0,
                        pointHitRadius: 16,
                    },
                    {
                        label: 'Jumlah Kontribusi',
                        data: {!! json_encode($chartClaims) !!},
                        borderColor: '#e8a23e',
                        backgroundColor: 'rgba(232, 162, 62, 0.06)',
                        fill: false,
                        yAxisID: 'y1',
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 0,
                        pointHitRadius: 16,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        labels: {
                            boxWidth: 12,
                            boxHeight: 2,
                            color: '#78716c',
                            font: { size: 11, family: 'Manrope' },
                            padding: 16,
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: '#f0ebe0' },
                        ticks: { color: '#a8a29e', font: { size: 11, family: 'Manrope' } },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f0ebe0' },
                        ticks: {
                            color: '#a8a29e',
                            font: { size: 11, family: 'Manrope' },
                            callback: value => 'Rp ' + new Intl.NumberFormat('id-ID').format(value)
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { color: '#a8a29e', font: { size: 11, family: 'Manrope' } },
                    }
                }
            }
        });
    }
</script>
@endpush
