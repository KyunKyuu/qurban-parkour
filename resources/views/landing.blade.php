<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PARQOUR — Patungan Riungan Qurban</title>
    <link rel="icon" type="image/png" href="{{ asset('images/figma/mlup.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,700;1,9..144,400;1,9..144,700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; }
        body { font-family: 'Manrope', sans-serif; background: #faf9f6; color: #1a1a1a; }
        .display-font { font-family: 'Fraunces', serif; }

        #navbar { transition: background 0.35s ease, box-shadow 0.35s ease; }
        #navbar.scrolled { background: rgba(250,249,246,0.95); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); box-shadow: 0 1px 0 rgba(0,0,0,0.07); }

        .tr { transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1); }
        .btn:active { transform: scale(0.98) translateY(1px); }

        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-14px); } }
        .sheep-float { animation: float 5s ease-in-out infinite; }

        @keyframes leaf-sway {
            0%, 100% { transform: rotate(var(--lr, 0deg)) translateY(0px); }
            32%  { transform: rotate(calc(var(--lr, 0deg) + 13deg)) translateY(-13px); }
            68%  { transform: rotate(calc(var(--lr, 0deg) -  8deg)) translateY(6px); }
        }
        .leaf { animation: leaf-sway var(--ld, 4.5s) ease-in-out infinite var(--ldelay, 0s);
                pointer-events: none; position: absolute; will-change: transform; }

        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
        .blink { animation: blink 1.2s ease-in-out infinite; }

        .fade-up { opacity: 0; transform: translateY(28px); transition: opacity 0.65s ease, transform 0.65s cubic-bezier(0.16, 1, 0.3, 1); }
        .fade-up.visible { opacity: 1; transform: translateY(0); }

        .step-card { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
        .step-card:hover { transform: translateY(-6px); box-shadow: 0 24px 48px rgba(27,67,50,0.13); }

        .progress-fill { width: 0; transition: width 1.8s cubic-bezier(0.16, 1, 0.3, 1); }

details > summary { list-style: none; cursor: pointer; }
        details > summary::-webkit-details-marker { display: none; }
        .faq-icon { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
        details[open] .faq-icon { transform: rotate(45deg); }
        details[open] .faq-answer { animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

        @keyframes audio-ring {
            0%   { box-shadow: 0 4px 14px rgba(27,67,50,0.32), 0 0 0 0 rgba(27,67,50,0.40); }
            70%  { box-shadow: 0 4px 14px rgba(27,67,50,0.32), 0 0 0 10px rgba(27,67,50,0); }
            100% { box-shadow: 0 4px 14px rgba(27,67,50,0.32), 0 0 0 0 rgba(27,67,50,0); }
        }
        #audio-toggle.playing { animation: audio-ring 1.6s ease-out infinite; }

        @media (max-width: 767px) {
            .program-grid, .harga-grid, .cta-grid { grid-template-columns: 1fr !important; }
        }
    </style>
</head>
<body>

{{-- ═══════════════ NAVBAR ═══════════════ --}}
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 bg-transparent">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
        <a href="{{ route('landing') }}" class="display-font text-2xl font-bold tracking-tight text-[#1b4332]">PARQOUR</a>
        <div class="hidden items-center gap-7 md:flex">
            <a href="#program" class="tr text-sm font-medium text-stone-500 hover:text-[#1b4332]">Program</a>
            <a href="#cara-berdonasi" class="tr text-sm font-medium text-stone-500 hover:text-[#1b4332]">Cara Berdonasi</a>
            <a href="#harga" class="tr text-sm font-medium text-stone-500 hover:text-[#1b4332]">Harga</a>
            <a href="#faq" class="tr text-sm font-medium text-stone-500 hover:text-[#1b4332]">FAQ</a>
        </div>
        <div class="flex items-center gap-2">

            <a href="https://wa.me/6285782876666?text=Assalamu%27alaikum%2C+saya+ingin+konfirmasi+donasi+PARQOUR..." target="_blank" rel="noopener" class="btn tr rounded-full bg-[#1b4332] px-5 py-2.5 text-sm font-semibold text-amber-100 hover:bg-[#0f2d1e] hover:-translate-y-0.5 shadow-[0_4px_16px_rgba(27,67,50,0.28)]">
                Berkurban Sekarang
            </a>
            <a href="{{ route('login') }}" title="Login Admin / PIC"
               class="btn tr flex items-center gap-1.5 rounded-full border border-stone-300 bg-white px-2.5 py-2 md:px-3 text-xs font-semibold text-stone-500 hover:border-[#1b4332] hover:text-[#1b4332]">
                <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>
                </svg>
                <span class="hidden md:inline">Masuk</span>
            </a>
        </div>
    </div>
</nav>

{{-- ═══════════════ HERO ═══════════════ --}}
<section class="relative flex min-h-[100dvh] flex-col items-center overflow-hidden bg-[#faf9f6] px-6 pt-40 pb-0 md:pt-52">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-10%,rgba(27,67,50,0.07),transparent)]"></div>

    {{-- ── Decorative leaves ── --}}
    @php
    $leaves = [
        ['x'=>'3%',  'y'=>'18%', 'w'=>52, 'c'=>'#2d6a4f', 'o'=>0.50, 'r'=>'-28deg', 'd'=>'4.6s', 'dl'=>'0s'],
        ['x'=>'1%',  'y'=>'52%', 'w'=>66, 'c'=>'#1b4332', 'o'=>0.40, 'r'=>'-42deg', 'd'=>'5.3s', 'dl'=>'1.1s'],
        ['x'=>'7%',  'y'=>'74%', 'w'=>38, 'c'=>'#40916c', 'o'=>0.38, 'r'=>'18deg',  'd'=>'3.9s', 'dl'=>'0.6s'],
        ['x'=>'12%', 'y'=>'7%',  'w'=>30, 'c'=>'#52b788', 'o'=>0.30, 'r'=>'8deg',   'd'=>'4.1s', 'dl'=>'0.3s'],
        ['x'=>'88%', 'y'=>'13%', 'w'=>44, 'c'=>'#1b4332', 'o'=>0.45, 'r'=>'32deg',  'd'=>'4.9s', 'dl'=>'0.9s'],
        ['x'=>'93%', 'y'=>'42%', 'w'=>58, 'c'=>'#2d6a4f', 'o'=>0.38, 'r'=>'22deg',  'd'=>'5.5s', 'dl'=>'2.0s'],
        ['x'=>'85%', 'y'=>'68%', 'w'=>36, 'c'=>'#40916c', 'o'=>0.35, 'r'=>'-34deg', 'd'=>'4.3s', 'dl'=>'1.5s'],
        ['x'=>'78%', 'y'=>'6%',  'w'=>28, 'c'=>'#52b788', 'o'=>0.28, 'r'=>'-12deg', 'd'=>'4.7s', 'dl'=>'1.8s'],
    ];
    @endphp
    @foreach($leaves as $leaf)
    <div class="leaf" style="left:{{ $leaf['x'] }}; top:{{ $leaf['y'] }}; width:{{ $leaf['w'] }}px; height:{{ round($leaf['w']*1.68) }}px; opacity:{{ $leaf['o'] }}; --lr:{{ $leaf['r'] }}; --ld:{{ $leaf['d'] }}; --ldelay:{{ $leaf['dl'] }};">
        <svg viewBox="0 0 24 40" fill="none" width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 1 C21 9 23 23 12 39 C1 23 3 9 12 1Z" fill="{{ $leaf['c'] }}"/>
            <path d="M12 1 L12 39" stroke="rgba(255,255,255,0.22)" stroke-width="0.9"/>
            <path d="M12 14 Q17 11 19 15" stroke="rgba(255,255,255,0.14)" stroke-width="0.6" fill="none"/>
            <path d="M12 23 Q17 20 19 24" stroke="rgba(255,255,255,0.14)" stroke-width="0.6" fill="none"/>
            <path d="M12 14 Q7 11 5 15"  stroke="rgba(255,255,255,0.14)" stroke-width="0.6" fill="none"/>
            <path d="M12 23 Q7 20 5 24"  stroke="rgba(255,255,255,0.14)" stroke-width="0.6" fill="none"/>
        </svg>
    </div>
    @endforeach

    <div class="relative z-10 flex flex-col items-center text-center">
        <span class="text-xs font-semibold uppercase tracking-[0.26em] text-stone-400">
            MLUP Academy × 7 Komunitas Muslimah
        </span>

        <h1 class="display-font mt-4 leading-[0.95] tracking-tight text-stone-950" style="font-size: clamp(5rem, 18vw, 14rem);">
            PARQOUR
        </h1>
        <p class="display-font mt-2 text-2xl font-normal italic text-[#1b4332] md:text-3xl">
            Patungan Riungan Qurban
        </p>

        <p class="mt-5 max-w-sm text-base leading-relaxed text-stone-500">
            Berapapun nominalnya, pengorbananmu nyata.
        </p>

        <div class="mt-6 inline-flex flex-wrap items-center justify-center gap-x-3 gap-y-2 rounded-2xl border border-stone-200 bg-white px-5 py-3 shadow-[0_2px_12px_rgba(0,0,0,0.06)]">
            <span class="text-xs font-medium text-stone-400">Idul Adha 1447 H · 27 Mei 2026</span>
            <span class="text-[#c8973a]">·</span>
            <span id="countdown" class="font-mono text-base font-bold tracking-wide text-[#1b4332]"></span>
        </div>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="https://wa.me/6285782876666?text=Assalamu%27alaikum%2C+saya+ingin+konfirmasi+donasi+PARQOUR..." target="_blank" rel="noopener" class="btn tr rounded-full bg-[#1b4332] px-7 py-3.5 text-sm font-semibold text-amber-100 hover:bg-[#0f2d1e] hover:-translate-y-0.5 shadow-[0_8px_28px_rgba(27,67,50,0.30)]">
                Mulai Berkurban
            </a>
            <a href="#program" class="btn tr rounded-full border border-stone-300 bg-white px-7 py-3.5 text-sm font-semibold text-stone-700 hover:border-[#1b4332] hover:text-[#1b4332]">
                Pelajari Program
            </a>
        </div>
    </div>

    <div class="relative z-10 mt-10 w-full max-w-xl">
        <img src="{{ asset('images/figma/hero-sheep.png') }}" alt="Hewan Qurban" class="sheep-float w-full object-contain drop-shadow-[0_32px_48px_rgba(27,67,50,0.18)]" style="max-height: 420px; object-position: center bottom;">
    </div>
</section>

{{-- ═══════════════ QUOTE ═══════════════ --}}
<section class="relative overflow-hidden bg-white px-6 py-24 md:py-36">
    {{-- Daun kecil sudut kanan bawah --}}
    @php $qLeaves = [
        ['r'=>'1%',  'b'=>'6%',  'w'=>46, 'c'=>'#2d6a4f', 'o'=>0.17, 'rot'=>'24deg',  'd'=>'5.4s', 'dl'=>'0s'],
        ['r'=>'5%',  'b'=>'18%', 'w'=>30, 'c'=>'#40916c', 'o'=>0.12, 'rot'=>'42deg',  'd'=>'4.2s', 'dl'=>'0.9s'],
        ['r'=>'9%',  'b'=>'4%',  'w'=>20, 'c'=>'#52b788', 'o'=>0.09, 'rot'=>'14deg',  'd'=>'6.0s', 'dl'=>'1.6s'],
    ]; @endphp
    @foreach($qLeaves as $l)
    <div class="leaf" style="right:{{ $l['r'] }}; bottom:{{ $l['b'] }}; left:auto; top:auto; width:{{ $l['w'] }}px; height:{{ round($l['w']*1.68) }}px; opacity:{{ $l['o'] }}; --lr:{{ $l['rot'] }}; --ld:{{ $l['d'] }}; --ldelay:{{ $l['dl'] }};"><svg viewBox="0 0 24 40" fill="none" width="100%" height="100%"><path d="M12 1 C21 9 23 23 12 39 C1 23 3 9 12 1Z" fill="{{ $l['c'] }}"/><path d="M12 1 L12 39" stroke="rgba(255,255,255,0.22)" stroke-width="0.9"/></svg></div>
    @endforeach
    <div class="mx-auto max-w-4xl">
        <div class="mb-10 h-px w-14 bg-[#c8973a]"></div>
        <blockquote
            id="quote-fill-text"
            class="display-font text-4xl leading-[1.5] md:text-6xl"
            style="background: linear-gradient(to bottom, #1c1917 var(--q, 0%), #d6d3d1 var(--q, 0%)); -webkit-background-clip: text; background-clip: text; color: transparent;">
            "Qurban adalah puncak pengorbanan seorang muslim. Tapi tidak semua dari kita diberi keleluasaan untuk menunaikannya sendiri, Please, jangan jadiin itu halangan."
        </blockquote>
        <div class="mt-10 h-px w-14 bg-[#c8973a]"></div>
    </div>
</section>

{{-- ═══════════════ PROGRAM ═══════════════ --}}
<section id="program" class="bg-[#faf9f6] px-6 py-20 md:py-28">
    <div class="mx-auto max-w-6xl">
        <div class="program-grid grid items-center gap-14 lg:grid-cols-[0.95fr_1.05fr]">
            <div class="fade-up order-2 lg:order-1">
                <div class="overflow-hidden rounded-[2.5rem] bg-stone-100">
                    <img src="{{ asset('images/figma/program-cow.png') }}" alt="Program Qurban" class="tr w-full object-contain hover:scale-[1.03]" style="max-height: 500px;">
                </div>
            </div>

            <div class="fade-up order-1 lg:order-2">
                <span class="text-xs font-semibold uppercase tracking-[0.28em] text-[#c8973a]">Tentang Program</span>
                <h2 class="display-font mt-4 text-4xl leading-tight tracking-tight text-stone-950 md:text-5xl">
                1000 Paket Daging Qurban<br> untuk Mahasiswa yang Membutuhkan
                </h2>
                <div class="mt-6 space-y-4 text-base leading-relaxed text-stone-500">
                    <p>PARQOUR hadir sebagai ruang bersama: berapapun yang bisa kamu sisihkan, kita kumpulkan, kita riungkan, kita wujudkan jadi hewan qurban, dan dagingnya kita salurkan ke 1000 mahasiswa yang membutuhkan</p>
                    <p>Bersama 7 komunitas muslimah, kita bergerak jadi satu.</p>
                </div>
                <div class="mt-8 flex flex-wrap items-center gap-6">
                    <a href="https://wa.me/6285782876666?text=Assalamu%27alaikum%2C+saya+ingin+konfirmasi+donasi+PARQOUR..." target="_blank" rel="noopener" class="btn tr inline-flex items-center gap-2 rounded-full bg-[#1b4332] px-6 py-3 text-sm font-semibold text-amber-100 hover:bg-[#0f2d1e]">
                        Ikut Berqurban
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    <div class="flex items-center gap-2 text-sm text-stone-400">
                        <svg class="h-4 w-4 text-[#c8973a]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Laporan transparan via WhatsApp
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════ 4 CARA BERDONASI ═══════════════ --}}
<section id="cara-berdonasi" class="relative overflow-hidden bg-stone-100/80 px-6 py-20 md:py-28">
    {{-- Daun kecil sudut kanan atas --}}
    @php $cbLeaves = [
        ['r'=>'1%',  't'=>'8%',  'w'=>50, 'c'=>'#2d6a4f', 'o'=>0.16, 'rot'=>'-32deg', 'd'=>'4.8s', 'dl'=>'0.3s'],
        ['r'=>'5%',  't'=>'4%',  'w'=>32, 'c'=>'#40916c', 'o'=>0.12, 'rot'=>'-18deg', 'd'=>'5.6s', 'dl'=>'1.1s'],
        ['l'=>'1%',  'b'=>'10%', 'w'=>28, 'c'=>'#1b4332', 'o'=>0.10, 'rot'=>'28deg',  'd'=>'4.4s', 'dl'=>'0.7s'],
    ]; @endphp
    @foreach($cbLeaves as $l)
    @php
        $pos = '';
        if (isset($l['r'])) $pos .= "right:{$l['r']}; left:auto; ";
        if (isset($l['l'])) $pos .= "left:{$l['l']}; ";
        if (isset($l['t'])) $pos .= "top:{$l['t']}; bottom:auto; ";
        if (isset($l['b'])) $pos .= "bottom:{$l['b']}; top:auto; ";
    @endphp
    <div class="leaf" style="{{ $pos }}width:{{ $l['w'] }}px; height:{{ round($l['w']*1.68) }}px; opacity:{{ $l['o'] }}; --lr:{{ $l['rot'] }}; --ld:{{ $l['d'] }}; --ldelay:{{ $l['dl'] }};"><svg viewBox="0 0 24 40" fill="none" width="100%" height="100%"><path d="M12 1 C21 9 23 23 12 39 C1 23 3 9 12 1Z" fill="{{ $l['c'] }}"/><path d="M12 1 L12 39" stroke="rgba(255,255,255,0.22)" stroke-width="0.9"/></svg></div>
    @endforeach
    <div class="mx-auto max-w-6xl">
        <div class="text-center fade-up">
            <span class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-400">4 Langkah</span>
            <h2 class="display-font mt-3 text-4xl tracking-tight text-stone-950 md:text-5xl">Cara Berdonasi</h2>
        </div>

        @php
        $steps = [
            [
                'num'   => '01',
                'title' => 'Niatkan',
                'desc'  => 'Tentukan nominal yang ingin kamu transfer. Berapapun itu.',
            ],
            [
                'num'   => '02',
                'title' => 'Transfer',
                'desc'  => 'ke BCA Digital / Blu perwakilan tim Muslim Level Up Academy',
            ],
            [
                'num'   => '03',
                'title' => 'Konfirmasi',
                'desc'  => 'Isi form konfirmasi atau langsung WA 0857-8287-6666',
            ],
            [
                'num'   => '04',
                'title' => 'Qurban Tuntas',
                'desc'  => 'Laporan dan dokumentasi kita update di instagram @muslimlup.ac.id',
            ],
        ];
        @endphp

        <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($steps as $i => $step)
            <div class="step-card fade-up rounded-[2rem] bg-white p-7 shadow-[0_2px_12px_rgba(0,0,0,0.05)]" style="transition-delay: {{ $i * 80 }}ms">
                <span class="display-font text-6xl font-bold leading-none text-[#1b4332]/10">{{ $step['num'] }}</span>
                <h3 class="mt-4 text-base font-bold text-stone-900">{{ $step['title'] }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-stone-500">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Rekening + Konfirmasi --}}
        <div class="fade-up mt-10 rounded-[2rem] bg-white p-6 shadow-[0_2px_12px_rgba(0,0,0,0.05)] md:p-8">
            <div class="flex flex-col items-start justify-between gap-5 md:flex-row md:items-center">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-400">Rekening Transfer</p>
                    <p class="mt-2 text-lg font-bold text-stone-900" id="rekening-text">{{ config('qurban.bank_account_label') }}</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <button onclick="copyRekening()" class="btn tr inline-flex items-center gap-2 rounded-full border border-stone-200 bg-stone-50 px-5 py-2.5 text-sm font-semibold text-stone-700 hover:border-[#1b4332] hover:text-[#1b4332]" id="copy-btn">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Salin Rekening
                    </button>
                    <a href="https://wa.me/6285782876666?text=Assalamu%27alaikum%2C+saya+ingin+konfirmasi+donasi+PARQOUR..." target="_blank" rel="noopener" class="btn tr inline-flex items-center gap-2 rounded-full bg-[#1b4332] px-5 py-2.5 text-sm font-semibold text-amber-100 hover:bg-[#0f2d1e]">
                        Sudah transfer? Konfirmasi di sini
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════ PROGRESS ═══════════════ --}}
@php
// $progressPct, $sheepCurrent, $totalSheep, $totalCollected — passed from route (web.php)
$clipInsetTop = 100 - $progressPct;
@endphp

<section class="relative overflow-hidden bg-white px-6 py-20 md:py-28">
    {{-- Daun kecil sudut kiri bawah (sisi berlawanan dari sheep) --}}
    @php $pgLeaves = [
        ['l'=>'1%', 'b'=>'12%', 'w'=>40, 'c'=>'#2d6a4f', 'o'=>0.14, 'rot'=>'-26deg', 'd'=>'5.0s', 'dl'=>'0s'],
        ['l'=>'5%', 'b'=>'4%',  'w'=>24, 'c'=>'#40916c', 'o'=>0.10, 'rot'=>'-44deg', 'd'=>'4.3s', 'dl'=>'1.2s'],
    ]; @endphp
    @foreach($pgLeaves as $l)
    <div class="leaf" style="left:{{ $l['l'] }}; bottom:{{ $l['b'] }}; top:auto; width:{{ $l['w'] }}px; height:{{ round($l['w']*1.68) }}px; opacity:{{ $l['o'] }}; --lr:{{ $l['rot'] }}; --ld:{{ $l['d'] }}; --ldelay:{{ $l['dl'] }};"><svg viewBox="0 0 24 40" fill="none" width="100%" height="100%"><path d="M12 1 C21 9 23 23 12 39 C1 23 3 9 12 1Z" fill="{{ $l['c'] }}"/><path d="M12 1 L12 39" stroke="rgba(255,255,255,0.22)" stroke-width="0.9"/></svg></div>
    @endforeach
    <div class="mx-auto max-w-6xl">
        <h2 class="display-font text-center text-4xl tracking-tight text-stone-950 md:text-5xl">Progress Program</h2>

        <div class="mt-12 grid grid-cols-1 items-center gap-10 md:grid-cols-[5fr_6fr] md:gap-14">

            {{-- ── Stats (kiri) ── --}}
            <div>
                <p class="display-font text-8xl font-bold leading-none text-stone-950 md:text-9xl">{{ $progressPct }}%</p>
                <p class="mt-2 text-sm text-stone-400">target terkumpul saat ini</p>

                <div class="mt-5 h-2 overflow-hidden rounded-full bg-stone-100">
                    <div class="progress-fill h-full rounded-full bg-[#c8973a]"></div>
                </div>

                @php
                $participantCount = \App\Models\Claim::where('verification_status', 'VERIFIED')
                    ->whereIn('category_type', ['DOMBA', 'PATUNGAN'])->count();
                $terkumpulLabel   = $totalCollected >= 1_000_000
                    ? 'Rp ' . number_format($totalCollected / 1_000_000, 1, ',', '.') . ' Jt'
                    : 'Rp ' . number_format($totalCollected, 0, ',', '.');
                $progressStats = [
                    ['val' => $participantCount,                  'label' => 'Peserta'],
                    ['val' => $terkumpulLabel,                    'label' => 'Terkumpul'],
                    ['val' => $sheepCurrent . '/' . $totalSheep, 'label' => 'Total Hewan'],
                    ['val' => '27 Mei',                          'label' => 'Hari Qurban'],
                ];
                @endphp
                <div class="mt-6 grid grid-cols-2 gap-4">
                    @foreach($progressStats as $stat)
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-5 shadow-[0_2px_8px_rgba(0,0,0,0.04)]">
                        <p class="display-font text-3xl font-bold text-stone-900">{{ $stat['val'] }}</p>
                        <p class="mt-1.5 text-sm text-stone-400">{{ $stat['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Siluet domba (kanan) ── --}}
            <div class="flex flex-col items-center gap-4">
                <div class="relative w-full" style="aspect-ratio: 1.09; max-height: 600px;">
                    {{-- Layer 1: siluet grey (base) --}}
                    <img src="{{ asset('images/figma/progress/Whole_sheep_facing_forward_202605092312 1.png') }}"
                         alt="" aria-hidden="true"
                         class="absolute inset-0 h-full w-full object-contain"
                         style="filter: grayscale(1) contrast(0) brightness(1.6);">
                    {{-- Layer 2: gambar asli, terungkap dari bawah via clip-path --}}
                    <img src="{{ asset('images/figma/progress/Whole_sheep_facing_forward_202605092312 1.png') }}"
                         alt="Domba qurban"
                         id="sheep-image-fill"
                         class="absolute inset-0 h-full w-full object-contain"
                         style="clip-path: inset(100% 0 0 0); transition: clip-path 1.8s cubic-bezier(0.16, 1, 0.3, 1);">
                </div>
                <p class="text-sm text-stone-400">{{ $sheepCurrent }} dari {{ $totalSheep }} domba</p>
            </div>

        </div>
    </div>
</section>

{{-- ═══════════════ HARGA HEWAN KURBAN ═══════════════ --}}
<section id="harga" class="bg-[#faf9f6] px-6 py-20 md:py-28">
    <div class="mx-auto max-w-6xl">
        <div class="mb-10 fade-up">
            <span class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-400">Pilih Hewan Kurban</span>
            <h2 class="display-font mt-3 text-4xl tracking-tight text-stone-950 md:text-5xl">Harga Hewan Kurban</h2>
        </div>

        @php
        $categories = config('qurban.categories');
        $catImages = [
            'DOMBA'    => asset('images/figma/harga-domba.png'),
            'SAPI'     => asset('images/figma/harga-sapi.png'),
            'SAPI_1_7' => asset('images/figma/harga-sapi-7.png'),
            'PATUNGAN' => asset('images/figma/harga-patungan.png'),
        ];
        // screen blend untuk gambar dengan background hitam; normal untuk PNG transparan
        $catBlend = [
            'DOMBA'    => 'normal',
            'SAPI'     => 'normal',
            'SAPI_1_7' => 'normal',
            'PATUNGAN' => 'normal',
        ];
        $firstKey = array_key_first($categories);
        @endphp

        <div class="grid items-start gap-8 lg:grid-cols-[1fr_1fr]">
            {{-- Kiri: image switcher dengan shape hijau di belakang --}}
            <div class="fade-up sticky top-28">
                {{-- overflow: visible agar gambar bisa keluar dari batas container --}}
                <div class="relative mx-auto" style="aspect-ratio: 3/2; max-height: 360px; overflow: visible;">
                    {{-- Shape hijau di belakang image --}}
                    <div class="absolute rounded-[2.5rem] z-0"
                         style="top: 12%; left: 6%; right: 6%; bottom: 12%; background: #5E8647;"></div>
                    {{-- Images: scale 110% agar meluber keluar shape --}}
                    @foreach($categories as $key => $cat)
                    <img
                        id="img-{{ $key }}"
                        src="{{ $catImages[$key] ?? asset('images/figma/harga-domba.png') }}"
                        alt="{{ $cat['label'] }}"
                        class="absolute inset-0 h-full w-full object-contain z-10 transition-all duration-500"
                        style="mix-blend-mode: {{ $catBlend[$key] ?? 'normal' }}; transform-origin: center; opacity: {{ $key === $firstKey ? '1' : '0' }}; transform: scale({{ $key === $firstKey ? '1.1' : '1.04' }});"
                    >
                    @endforeach
                </div>
            </div>

            {{-- Kanan: daftar kartu --}}
            <div class="fade-up space-y-3">
                @foreach($categories as $key => $cat)
                <div
                    id="card-{{ $key }}"
                    onclick="selectCategory('{{ $key }}')"
                    class="harga-card cursor-pointer rounded-[1.5rem] border p-5 tr"
                    style="{{ $key === $firstKey
                        ? 'border-color:#1b4332; background:#f0f7f4; box-shadow: 0 16px 40px rgba(27,67,50,0.16);'
                        : 'border-color:#e7e5e4; background:white; box-shadow: 0 2px 8px rgba(0,0,0,0.04);' }}"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-stone-900">{{ $cat['label'] }}</p>
                            <p class="mt-1 text-sm leading-relaxed text-stone-500">{{ $cat['description'] }}</p>
                        </div>
                        <div class="flex-shrink-0 rounded-xl bg-stone-100 px-3 py-2 text-right">
                            @if($cat['price'] > 0)
                            <p class="text-xs text-stone-400 leading-none">Rp</p>
                            <p class="mt-0.5 text-sm font-bold text-stone-800">{{ number_format($cat['price'], 0, ',', '.') }}</p>
                            @else
                            <p class="text-sm font-bold text-stone-700">Nominal<br>bebas</p>
                            @endif
                        </div>
                    </div>

                    {{-- Expanded detail (shown when active) --}}
                    <div id="detail-{{ $key }}" class="overflow-hidden transition-all duration-400" style="{{ $key === $firstKey ? 'max-height:200px; margin-top:1rem;' : 'max-height:0;' }}">
                        <div class="border-t border-stone-100 pt-4">
                            <a href="https://wa.me/6285782876666?text=Assalamu%27alaikum%2C+saya+ingin+berqurban+PARQOUR+kategori+{{ urlencode($cat['label']) }}..." target="_blank" rel="noopener" class="btn tr mt-4 inline-block w-full rounded-2xl bg-[#1b4332] py-3 text-center text-sm font-semibold text-amber-100 hover:bg-[#0f2d1e]">
                                Pilih {{ $cat['label'] }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════ KOMUNITAS ═══════════════ --}}
<section class="bg-white px-6 py-16 md:py-20">
    <div class="mx-auto max-w-5xl">
        <div class="text-center fade-up">
            <span class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-400">Didukung Oleh</span>
            <h2 class="display-font mt-3 text-3xl tracking-tight text-stone-950">Community Support</h2>
        </div>

        @php
        $komunitas = [
            ['src' => asset('images/komunitas/Logo.png'),                    'name' => 'Bliss Community'],
            ['src' => asset('images/komunitas/new gemusi.png'),              'name' => 'Gemusi'],
            ['src' => asset('images/komunitas/Logo Ruang Alara (1).png'),   'name' => 'Ruang Alara'],
            ['src' => asset('images/komunitas/logo ufairah.jpg.jpeg'),      'name' => 'Gen Ufairah'],
            ['src' => asset('images/komunitas/Logo Craftiva.JPG.jpeg'),     'name' => 'Craftiva'],
            ['src' => asset('images/komunitas/rest area.png'),              'name' => 'Rest Area'],
            ['src' => asset('images/komunitas/Logo Hawa Community.png'),    'name' => 'Hawa Community'],
        ];
        @endphp

        <div class="mt-10 grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-7">
            @foreach($komunitas as $i => $k)
            <div class="fade-up flex flex-col items-center gap-3" style="transition-delay: {{ $i * 60 }}ms">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl border border-stone-200 bg-stone-50 p-3 shadow-sm tr hover:border-[#1b4332]/30 hover:shadow-md">
                    <img src="{{ $k['src'] }}" alt="{{ $k['name'] }}" class="max-h-12 w-auto object-contain">
                </div>
                <p class="text-center text-xs font-semibold leading-tight text-stone-600">{{ $k['name'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════ TENTANG MLUP ACADEMY ═══════════════ --}}
<section class="bg-[#faf9f6] px-6 py-16 md:py-20">
    <div class="mx-auto max-w-4xl fade-up">
        <div class="rounded-[2.5rem] border border-stone-200 bg-white p-8 shadow-[0_4px_24px_rgba(0,0,0,0.05)] md:p-12">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/figma/mlup.png') }}" alt="MLUP Academy" class="h-12 w-12 rounded-xl object-contain">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-[0.28em] text-[#c8973a]">Tentang Penyelenggara</span>
                    <h2 class="display-font mt-0.5 text-3xl tracking-tight text-stone-950 md:text-4xl">MLUP Academy</h2>
                </div>
            </div>
            <div class="mt-5 space-y-4 text-sm leading-relaxed text-stone-500 md:text-base">
                <p>MLUP (Muslim Level Up Community) adalah lembaga non-profit di bawah Yayasan Akselerasi Insan Indonesia yang mendorong pemikiran Islam, peradaban, dan kegiatan sosial kemahasiswaan. Berbasis di Bandung, aktif menjangkau komunitas akademik seluruh Indonesia.</p>
                <p>Sebelumnya kami telah menyalurkan program sosial di bulan Ramadhan: bekerja sama & bantuan pendidikan / UKT untuk mahasiswa.</p>
            </div>
            <div class="mt-7 flex flex-wrap gap-4 text-sm">
                <a href="https://instagram.com/muslimlup.ac.id" target="_blank" rel="noopener" class="btn tr inline-flex items-center gap-2 rounded-full border border-stone-200 px-5 py-2.5 font-semibold text-stone-700 hover:border-[#1b4332] hover:text-[#1b4332]">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    @muslimlup.ac.id
                </a>
                <a href="tel:0857-8287-6666" class="btn tr inline-flex items-center gap-2 rounded-full border border-stone-200 px-5 py-2.5 font-semibold text-stone-700 hover:border-[#1b4332] hover:text-[#1b4332]">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    0857-8287-6666
                </a>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════ FAQ ═══════════════ --}}
<section id="faq" class="relative overflow-hidden bg-stone-100/80 px-6 py-20 md:py-28">
    {{-- Daun kecil sudut kanan bawah --}}
    @php $fqLeaves = [
        ['r'=>'2%', 'b'=>'8%',  'w'=>42, 'c'=>'#1b4332', 'o'=>0.15, 'rot'=>'30deg',  'd'=>'5.1s', 'dl'=>'0.4s'],
        ['r'=>'6%', 'b'=>'18%', 'w'=>26, 'c'=>'#2d6a4f', 'o'=>0.11, 'rot'=>'48deg',  'd'=>'4.6s', 'dl'=>'1.3s'],
    ]; @endphp
    @foreach($fqLeaves as $l)
    <div class="leaf" style="right:{{ $l['r'] }}; bottom:{{ $l['b'] }}; left:auto; top:auto; width:{{ $l['w'] }}px; height:{{ round($l['w']*1.68) }}px; opacity:{{ $l['o'] }}; --lr:{{ $l['rot'] }}; --ld:{{ $l['d'] }}; --ldelay:{{ $l['dl'] }};"><svg viewBox="0 0 24 40" fill="none" width="100%" height="100%"><path d="M12 1 C21 9 23 23 12 39 C1 23 3 9 12 1Z" fill="{{ $l['c'] }}"/><path d="M12 1 L12 39" stroke="rgba(255,255,255,0.22)" stroke-width="0.9"/></svg></div>
    @endforeach
    <div class="mx-auto max-w-2xl">
        <div class="text-center fade-up">
            <span class="text-xs font-semibold uppercase tracking-[0.28em] text-stone-400">Ada yang Ingin Ditanyakan?</span>
            <h2 class="display-font mt-3 text-4xl tracking-tight text-stone-950">Pertanyaan Umum</h2>
        </div>

        @php
        $faqs = [
            [
                'q' => 'Apakah qurban patungan sah secara syar\'i?',
                'a' => 'Iya — untuk domba/kambing sah 1 orang 1 ekor. Untuk sapi, satu ekor bisa untuk 7 orang. Kami menerima dan menyalurkan dalam kondisi satu hewan untuk satu kambing.',
            ],
            [
                'q' => 'Berapa minimal donasi?',
                'a' => 'Tidak ada batas minimal, berapapun yang bisa kamu sisihkan, kita kumpulkan, kita riungkan, kita wujudkan jadi hewan qurban, dan dagingnya kita salurkan ke 1000 mahasiswa yang membutuhkan',
            ],
            [
                'q' => 'Bagaimana cara konfirmasi donasi?',
                'a' => 'Isi form konfirmasi di halaman ini atau hubungi langsung Bustan via WhatsApp 0857-8287-6666.',
            ],
            [
                'q' => 'Kapan donasi ditutup?',
                'a' => 'Donasi tutup pada 30 Mei 2026 atau 13 Dzulhijjah 1447 H. ',
            ],
            [
                'q' => 'Di mana hewan qurban disalurkan?',
                'a' => 'Paket 1000 daging Qurban akan disalurkan kepada para mahasiswa yang membutuhkan di Kota Bandung dan sekitarnya.',
            ],
            [
                'q' => 'Adakah laporan penyaluran?',
                'a' => 'Ya — dokumentasi foto dan laporan transparan akan dikirim via WhatsApp dan diposting di Instagram @muslimlup.ac.id.',
            ],
            [
                'q' => 'Jika donasi melebihi target, ke mana sisanya?',
                'a' => 'Akan digunakan untuk menambah hewan qurban atau dialokasikan pada program sosial MLUP berikutnya, dengan laporan transparan.',
            ],
        ];
        @endphp

        <div class="mt-12 space-y-2">
            @foreach($faqs as $i => $faq)
            <details class="fade-up group rounded-2xl bg-white shadow-[0_1px_4px_rgba(0,0,0,0.05)]" style="transition-delay: {{ $i * 55 }}ms">
                <summary class="flex items-center justify-between gap-4 px-6 py-5 font-semibold text-stone-900">
                    <span class="text-sm leading-snug">{{ $faq['q'] }}</span>
                    <span class="faq-icon flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-stone-100 text-stone-500 group-open:bg-[#1b4332] group-open:text-white tr">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </span>
                </summary>
                <div class="faq-answer px-6 pb-6 text-sm leading-relaxed text-stone-500">{{ $faq['a'] }}</div>
            </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════ SIAP BERQURBAN ═══════════════ --}}
<section class="relative overflow-hidden bg-[#1b4332] px-6 py-20 md:py-28">

    {{-- Daun putih transparan di sisi kiri (area teks) --}}
    @php $ctaLeaves = [
        ['l'=>'1%', 't'=>'12%', 'w'=>48, 'c'=>'white', 'o'=>0.07, 'rot'=>'-22deg', 'd'=>'5.3s', 'dl'=>'0s'],
        ['l'=>'5%', 't'=>'35%', 'w'=>30, 'c'=>'white', 'o'=>0.05, 'rot'=>'-38deg', 'd'=>'4.7s', 'dl'=>'1.4s'],
        ['l'=>'2%', 'b'=>'15%', 'w'=>36, 'c'=>'white', 'o'=>0.06, 'rot'=>'-18deg', 'd'=>'6.0s', 'dl'=>'0.6s'],
    ]; @endphp
    @foreach($ctaLeaves as $l)
    @php
        $ctaPos = '';
        if (isset($l['l'])) $ctaPos .= "left:{$l['l']}; ";
        if (isset($l['t'])) $ctaPos .= "top:{$l['t']}; bottom:auto; ";
        if (isset($l['b'])) $ctaPos .= "bottom:{$l['b']}; top:auto; ";
    @endphp
    <div class="leaf" style="{{ $ctaPos }}width:{{ $l['w'] }}px; height:{{ round($l['w']*1.68) }}px; opacity:{{ $l['o'] }}; --lr:{{ $l['rot'] }}; --ld:{{ $l['d'] }}; --ldelay:{{ $l['dl'] }};"><svg viewBox="0 0 24 40" fill="none" width="100%" height="100%"><path d="M12 1 C21 9 23 23 12 39 C1 23 3 9 12 1Z" fill="{{ $l['c'] }}"/><path d="M12 1 L12 39" stroke="rgba(255,255,255,0.15)" stroke-width="0.9"/></svg></div>
    @endforeach

    {{-- Sapi: absolute kanan, bleeding off edge --}}
    <div class="pointer-events-none absolute bottom-0 right-0 top-0 hidden w-[48%] lg:block">
        <img src="{{ asset('images/figma/image 19.png') }}"
             alt=""
             class="h-full w-full object-cover"
             style="object-position: left center;">
        {{-- Gradient kiri: fade dari bg section ke transparent, sembunyikan black bg --}}
        <div class="absolute inset-0" style="background: linear-gradient(to right, #1b4332 0%, #1b4332 15%, transparent 50%);"></div>
    </div>

    <div class="relative z-10 mx-auto max-w-6xl">
        <div class="fade-up max-w-lg text-white">
            <h2 class="display-font text-5xl leading-tight tracking-tight md:text-6xl">
                Siap berqurban<br>bersama?
            </h2>
            <p class="mt-5 text-base leading-relaxed text-white/65">
                Donasikan sekarang dan konfirmasi langsung via WhatsApp.
            </p>
            <div class="mt-8 flex flex-col items-start gap-3">
                <a href="https://wa.me/6285782876666?text=Assalamu%27alaikum%2C+saya+ingin+konfirmasi+donasi+PARQOUR..." target="_blank" rel="noopener" class="btn tr inline-flex items-center gap-3 rounded-full bg-[#25D366] px-7 py-4 text-sm font-bold text-white hover:bg-[#20b858] hover:-translate-y-0.5 shadow-[0_8px_28px_rgba(37,211,102,0.35)]">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    Chat Bustan sekarang
                </a>
                <p class="text-xs text-white/40">0857-8287-6666 · Klik untuk buka WhatsApp</p>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════ FOOTER ═══════════════ --}}
<footer class="bg-[#0f2d1e] px-6 pb-0 pt-10">
    <div class="mx-auto max-w-6xl">
        <div class="rounded-2xl border border-white/10 bg-white/5 px-6 py-5 text-xs text-white/40 md:flex md:items-center md:justify-between md:gap-6">
            <div class="flex items-center gap-3">
                <span class="font-semibold text-white/60">MLUP</span>
                <span>·</span>
                <span>© 2026 MLUP Academy · Yayasan Akselerasi Insan Indonesia</span>
            </div>
            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 md:mt-0">
                <a href="https://instagram.com/muslimlup.ac.id" target="_blank" rel="noopener" class="tr hover:text-white/70">Instagram @muslimlup.ac.id</a>
                <span>·</span>
                <a href="tel:0857-8287-6666" class="tr hover:text-white/70">Kontak: 0857-8287-6666</a>
                <span>·</span>
                <a href="{{ route('login') }}" class="tr hover:text-white/70">Admin</a>
            </div>
        </div>
    </div>
    <p class="mt-5 text-center text-[11px] text-white/20">Created by Teguh Iqbal</p>
    <div class="mt-3 overflow-hidden select-none">
        <p class="display-font text-center font-bold leading-none tracking-tight text-white/5" style="font-size: clamp(4rem, 14vw, 12rem);">
            PARQOUR
        </p>
    </div>
</footer>

{{-- ═══════════════ FLOATING AUDIO PLAYER ═══════════════ --}}
<audio id="bg-audio" src="{{ asset('images/arab.mp3') }}" loop preload="none"></audio>

<div id="audio-player" class="fixed bottom-6 right-6 z-50">
    <div class="flex items-center gap-3 rounded-2xl border border-stone-200/80 bg-white/92 shadow-[0_8px_32px_rgba(0,0,0,0.14)]
                p-2 md:px-3 md:py-2.5"
         style="backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">

        {{-- Kepala sapi — desktop only --}}
        <div class="relative hidden md:block h-11 w-11 flex-shrink-0 overflow-hidden rounded-xl" style="background: #0f2d1e;">
            <img src="{{ asset('images/sapi-face.png') }}" alt=""
                 class="absolute inset-0 h-full w-full object-cover"
                 style="mix-blend-mode: screen; object-position: center top;">
        </div>

        {{-- Label — desktop only --}}
        <div class="hidden md:block min-w-0">
            <p class="text-xs font-bold leading-none text-stone-800">Ayo Dong</p>
            <p id="audio-status" class="mt-1 text-[10px] leading-none text-stone-400">Klik untuk putar</p>
        </div>

        {{-- Play / Pause button --}}
        <button id="audio-toggle" onclick="toggleAudio()"
            class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl md:rounded-full bg-[#1b4332] text-amber-100 shadow-[0_4px_14px_rgba(27,67,50,0.32)] transition hover:bg-[#0f2d1e] hover:scale-105 active:scale-95"
            aria-label="Putar musik">
            <svg id="icon-play" class="ml-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8 5v14l11-7z"/>
            </svg>
            <svg id="icon-pause" class="hidden h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
            </svg>
        </button>
    </div>
</div>

<script>
(function () {
    // ── Navbar scroll ──
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 50);
    }, { passive: true });

    // ── Countdown ──
    function tick() {
        const target = new Date('2026-05-27 23:59:59').getTime();
        const diff = target - Date.now();
        const el = document.getElementById('countdown');
        if (!el) return;
        if (diff <= 0) { el.textContent = 'Ditutup'; return; }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        el.textContent = `${d}h ${String(h).padStart(2,'0')}j ${String(m).padStart(2,'0')}m ${String(s).padStart(2,'0')}d`;
    }
    tick();
    setInterval(tick, 1000);

    // ── Quote fill on scroll ──
    const quoteFillEl = document.getElementById('quote-fill-text');
    if (quoteFillEl) {
        const updateQuote = () => {
            const rect  = quoteFillEl.closest('section').getBoundingClientRect();
            const wh    = window.innerHeight;
            // fill spans the entire duration the section is on screen
            const progress = Math.max(0, Math.min(1, (wh - rect.top) / (wh + rect.height)));
            quoteFillEl.style.setProperty('--q', (progress * 108).toFixed(1) + '%');
        };
        window.addEventListener('scroll', updateQuote, { passive: true });
        updateQuote();
    }

    // ── Fade-up on scroll ──
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
    }, { threshold: 0.1 });
    document.querySelectorAll('.fade-up').forEach(el => io.observe(el));

    // ── Progress bar + sheep silhouette fill ──
    const progressFill  = document.querySelector('.progress-fill');
    const sheepFillBar  = document.getElementById('sheep-image-fill');
    const progressPct   = {{ $progressPct }};

    if (progressFill) {
        const po = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (!e.isIntersecting) return;
                progressFill.style.width = progressPct + '%';
                if (sheepFillBar) sheepFillBar.style.clipPath = 'inset(' + (100 - progressPct) + '% 0 0 0)';
                po.unobserve(e.target);
            });
        }, { threshold: 0.25 });
        po.observe(progressFill.closest('section') ?? progressFill);
    }

    // ── Harga category switcher ──
    const categoryKeys = @json(array_keys(config('qurban.categories')));
    let activeKey = categoryKeys[0];

    window.selectCategory = function (key) {
        if (key === activeKey) return;

        // Fade out old image
        const oldImg = document.getElementById('img-' + activeKey);
        if (oldImg) { oldImg.style.opacity = '0'; oldImg.style.transform = 'scale(1.04)'; }

        // Collapse old detail
        const oldDetail = document.getElementById('detail-' + activeKey);
        if (oldDetail) { oldDetail.style.maxHeight = '0'; oldDetail.style.marginTop = '0'; }

        // Deactivate old card
        const oldCard = document.getElementById('card-' + activeKey);
        if (oldCard) {
            oldCard.style.borderColor = '#e7e5e4';
            oldCard.style.background = 'white';
            oldCard.style.boxShadow = '0 2px 8px rgba(0,0,0,0.04)';
        }

        activeKey = key;

        // Fade in new image
        const newImg = document.getElementById('img-' + key);
        if (newImg) { newImg.style.opacity = '1'; newImg.style.transform = 'scale(1.1)'; }

        // Expand new detail
        const newDetail = document.getElementById('detail-' + key);
        if (newDetail) { newDetail.style.maxHeight = '200px'; newDetail.style.marginTop = '1rem'; }

        // Activate new card
        const newCard = document.getElementById('card-' + key);
        if (newCard) {
            newCard.style.borderColor = '#1b4332';
            newCard.style.background = '#f0f7f4';
            newCard.style.boxShadow = '0 16px 40px rgba(27,67,50,0.16)';
        }
    };

    // ── Audio player ──
    window.toggleAudio = function () {
        const audio    = document.getElementById('bg-audio');
        const btn      = document.getElementById('audio-toggle');
        const playIcon = document.getElementById('icon-play');
        const pauseIcon= document.getElementById('icon-pause');
        const status   = document.getElementById('audio-status');

        if (audio.paused) {
            audio.play().then(() => {
                playIcon.classList.add('hidden');
                pauseIcon.classList.remove('hidden');
                btn.classList.add('playing');
                status.textContent = 'Sedang diputar...';
            }).catch(() => {});
        } else {
            audio.pause();
            playIcon.classList.remove('hidden');
            pauseIcon.classList.add('hidden');
            btn.classList.remove('playing');
            status.textContent = 'Klik untuk putar';
        }
    };

    // ── Copy rekening ──
    window.copyRekening = function () {
        navigator.clipboard.writeText('090109627811').then(() => {
            const btn = document.getElementById('copy-btn');
            btn.textContent = 'Tersalin!';
            setTimeout(() => { btn.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg> Salin Rekening'; }, 2000);
        });
    };
})();
</script>

</body>
</html>
