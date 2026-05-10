<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Manrope', sans-serif; }
        .display-font { font-family: 'Fraunces', serif; }
    </style>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.12),_transparent_28%),linear-gradient(180deg,_#fdfaf3,_#f5efe2)] text-stone-900">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-stone-200/60 bg-white/80 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <a href="{{ route('landing') }}" class="display-font text-2xl font-bold tracking-tight text-[#1b4332]">PARQOUR</a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="rounded-full border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-600 transition hover:border-stone-400 hover:text-stone-900">Masuk</a>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <div class="mx-auto max-w-6xl px-4 py-8 md:py-10">
                @yield('content')
            </div>
        </main>

        <footer class="bg-[#0f2d1e] px-6 pb-0 pt-10">
            <div class="mx-auto max-w-6xl">
                <div class="rounded-2xl border border-white/10 bg-white/5 px-6 py-5 text-xs text-white/40 md:flex md:items-center md:justify-between md:gap-6">
                    <div class="flex items-center gap-3">
                        <span class="font-semibold text-white/60">MLUP</span>
                        <span>·</span>
                        <span>© 2026 MLUP Academy · Yayasan Akselerasi Insan Indonesia</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 md:mt-0">
                        <a href="https://instagram.com/muslimlup.ac.id" target="_blank" rel="noopener" class="transition hover:text-white/70">Instagram @muslimlup.ac.id</a>
                        <span>·</span>
                        <a href="tel:0857-8287-6666" class="transition hover:text-white/70">Kontak: 0857-8287-6666</a>
                        <span>·</span>
                        <a href="{{ route('login') }}" class="transition hover:text-white/70">Admin</a>
                    </div>
                </div>
            </div>
            <div class="mt-6 overflow-hidden select-none">
                <p class="text-center font-bold leading-none tracking-tight text-white/5" style="font-family:'Fraunces',serif; font-size: clamp(4rem, 14vw, 12rem);">
                    PARQOUR
                </p>
            </div>
        </footer>
    </div>

    @yield('scripts')
</body>
</html>
