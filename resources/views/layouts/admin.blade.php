<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Admin') - {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/figma/mlup.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Manrope', sans-serif; }
        .display-font { font-family: 'Fraunces', serif; }
        .sidebar-transition { transition: transform 0.3s ease-in-out; }
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.sidebar-open { transform: translateX(0); }
        }
        @media (min-width: 769px) {
            #sidebar { transform: translateX(0) !important; }
        }
    </style>
</head>
<body class="bg-[#f5f2ea] text-stone-900">
    <div class="flex h-screen overflow-hidden">
        <aside id="sidebar" class="sidebar-transition fixed md:static inset-y-0 left-0 z-50 w-72 bg-emerald-900 text-emerald-50 flex flex-col">
            <div class="flex items-center justify-between h-16 px-5 border-b border-emerald-700/50">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.28em] text-orange-300/80">Dashboard Internal</p>
                    <h1 class="display-font text-xl">{{ config('app.name') }}</h1>
                </div>
                <button id="closeSidebar" class="md:hidden text-emerald-300 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-5 py-4 border-b border-emerald-700/50">
                <p class="text-sm font-semibold text-emerald-50">{{ auth()->user()->name }}</p>
                <p class="text-xs text-emerald-400">{{ auth()->user()->role }}</p>
            </div>

            <nav class="flex-1 px-3 py-4 space-y-6 overflow-y-auto">
                <div class="space-y-1">
                    <p class="px-3 text-[11px] uppercase tracking-[0.24em] text-emerald-400/70">Core</p>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-orange-400 text-white' : 'hover:bg-white/5 text-emerald-100' }}">
                        <span class="text-xs font-bold uppercase tracking-[0.2em]">DB</span>
                        <span class="font-medium">Dashboard Kurban</span>
                    </a>
                    <a href="{{ route('admin.financial-report') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.financial-report') ? 'bg-orange-400 text-white' : 'hover:bg-white/5 text-emerald-100' }}">
                        <span class="text-xs font-bold uppercase tracking-[0.2em]">LK</span>
                        <span class="font-medium">Laporan Keuangan</span>
                    </a>
                    <a href="{{ route('admin.claims.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.claims.*') ? 'bg-orange-400 text-white' : 'hover:bg-white/5 text-emerald-100' }}">
                        <span class="text-xs font-bold uppercase tracking-[0.2em]">KT</span>
                        <span class="font-medium">Kontribusi & Sertifikat</span>
                    </a>
                    <a href="{{ route('admin.exports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.exports.*') ? 'bg-orange-400 text-white' : 'hover:bg-white/5 text-emerald-100' }}">
                        <span class="text-xs font-bold uppercase tracking-[0.2em]">EX</span>
                        <span class="font-medium">Export Data</span>
                    </a>
                    <a href="{{ route('admin.settings.qurban.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.settings.qurban.*') ? 'bg-orange-400 text-white' : 'hover:bg-white/5 text-emerald-100' }}">
                        <span class="text-xs font-bold uppercase tracking-[0.2em]">ST</span>
                        <span class="font-medium">Settings Kurban</span>
                    </a>
                </div>

                <div class="space-y-1">
                    <p class="px-3 text-[11px] uppercase tracking-[0.24em] text-emerald-400/70">Operasional</p>
                    <a href="{{ route('admin.pics.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.pics.*') ? 'bg-orange-400 text-white' : 'hover:bg-white/5 text-emerald-100' }}">
                        <span class="text-xs font-bold uppercase tracking-[0.2em]">PJ</span>
                        <span class="font-medium">PJ (Person in Charge)</span>
                    </a>
                    <a href="{{ route('admin.communities.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.communities.*') ? 'bg-orange-400 text-white' : 'hover:bg-white/5 text-emerald-100' }}">
                        <span class="text-xs font-bold uppercase tracking-[0.2em]">KM</span>
                        <span class="font-medium">Komunitas</span>
                    </a>
                    <a href="{{ route('admin.fund-verification.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.fund-verification.*') ? 'bg-orange-400 text-white' : 'hover:bg-white/5 text-emerald-100' }}">
                        <span class="text-xs font-bold uppercase tracking-[0.2em]">TR</span>
                        <span class="font-medium">Verifikasi Transfer</span>
                    </a>
                </div>

                <div class="space-y-1">
                    <p class="px-3 text-[11px] uppercase tracking-[0.24em] text-emerald-400/70">Voucher</p>
                    <a href="{{ route('admin.vouchers.generate') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.vouchers.generate') ? 'bg-orange-400 text-white' : 'hover:bg-white/5 text-emerald-100' }}">
                        <span class="text-xs font-bold uppercase tracking-[0.2em]">GN</span>
                        <span class="font-medium">Generate Voucher</span>
                    </a>
                    <a href="{{ route('admin.vouchers.assign') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.vouchers.assign') ? 'bg-orange-400 text-white' : 'hover:bg-white/5 text-emerald-100' }}">
                        <span class="text-xs font-bold uppercase tracking-[0.2em]">AS</span>
                        <span class="font-medium">Assign ke PJ</span>
                    </a>
                    <a href="{{ route('admin.vouchers.print') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.vouchers.print*') ? 'bg-orange-400 text-white' : 'hover:bg-white/5 text-emerald-100' }}">
                        <span class="text-xs font-bold uppercase tracking-[0.2em]">PR</span>
                        <span class="font-medium">Print / QR Code</span>
                    </a>
                </div>

                <div class="rounded-2xl border border-emerald-700/50 bg-white/5 p-4 text-xs text-emerald-300">
                    <p class="font-semibold text-emerald-100">Legacy tool masih disimpan di backend.</p>
                    <p class="mt-2 leading-5">Flow merchant, redeem, dan voucher lama tidak lagi menjadi jalur utama bisnis pada versi kurban.</p>
                </div>
            </nav>

            <div class="px-4 py-4 border-t border-emerald-700/50">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl bg-white/5 px-4 py-3 text-left text-sm font-medium text-emerald-200 transition hover:bg-red-500 hover:text-white">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white border-b border-emerald-100">
                <div class="flex items-center justify-between h-16 px-4 md:px-6">
                    <div class="flex items-center gap-4">
                        <button id="menuButton" class="md:hidden text-emerald-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div>
                            <p class="text-xs uppercase tracking-[0.22em] text-emerald-500">Rebuild Phase 1</p>
                            <h2 class="text-lg font-semibold text-emerald-950">@yield('title', 'Dashboard')</h2>
                        </div>
                    </div>
                    <div class="hidden md:block text-sm text-emerald-500">{{ now()->format('d M Y') }}</div>
                </div>
            </header>

            @if(session('success'))
                <div class="mx-4 md:mx-6 mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mx-4 md:mx-6 mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <main class="flex-1 overflow-y-auto p-4 md:p-6">
                @yield('content')
                <footer class="mt-10 text-center text-xs text-emerald-400">
                    <p>{{ config('qurban.campaign_name') }} - Admin workspace</p>
                </footer>
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const menuButton = document.getElementById('menuButton');
        const closeSidebar = document.getElementById('closeSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('sidebar-open');
            sidebarOverlay.classList.remove('hidden');
        }

        function closeSidebarFunc() {
            sidebar.classList.remove('sidebar-open');
            sidebarOverlay.classList.add('hidden');
        }

        if (menuButton) menuButton.addEventListener('click', openSidebar);
        if (closeSidebar) closeSidebar.addEventListener('click', closeSidebarFunc);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebarFunc);
    </script>
    @stack('scripts')
</body>
</html>
