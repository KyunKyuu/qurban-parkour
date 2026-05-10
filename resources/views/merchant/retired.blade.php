<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Merchant Dipensiunkan - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Manrope', sans-serif; }
        .display-font { font-family: 'Fraunces', serif; }
    </style>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.14),_transparent_26%),linear-gradient(180deg,_#f8f5ee,_#efe4cf)] text-stone-900">
    <main class="mx-auto flex min-h-screen max-w-4xl items-center px-4 py-10">
        <section class="w-full rounded-[2.5rem] bg-stone-950 px-7 py-8 text-amber-100 shadow-[0_30px_90px_rgba(28,25,23,0.28)] md:px-10 md:py-10">
            <p class="text-sm uppercase tracking-[0.28em] text-amber-300/75">Legacy Flow Retired</p>
            <h1 class="display-font mt-4 text-4xl leading-tight md:text-5xl">Portal merchant lama sudah dipensiunkan.</h1>
            <p class="mt-5 max-w-2xl text-sm leading-8 text-amber-100/80">
                Rebuild fase ini memindahkan operasional utama ke flow kurban, sertifikat apresiasi, dan dashboard internal. Fitur scan, redeem, dan analytics merchant lama tidak lagi digunakan.
            </p>

            <div class="mt-8 grid gap-4 md:grid-cols-2">
                <div class="rounded-[1.6rem] border border-white/10 bg-white/5 p-5">
                    <p class="text-xs uppercase tracking-[0.22em] text-amber-200/70">Arah Sekarang</p>
                    <p class="mt-3 text-lg font-semibold text-white">Gunakan dashboard PIC atau admin untuk operasional kurban.</p>
                </div>
                <div class="rounded-[1.6rem] border border-white/10 bg-white/5 p-5">
                    <p class="text-xs uppercase tracking-[0.22em] text-amber-200/70">Butuh Bantuan</p>
                    <p class="mt-3 text-lg font-semibold text-white">Hubungi admin internal bila masih ada akun merchant aktif.</p>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('landing') }}" class="rounded-full bg-amber-300 px-6 py-3 text-sm font-semibold text-stone-950">Kembali ke Beranda</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-full border border-white/20 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                        Logout
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
