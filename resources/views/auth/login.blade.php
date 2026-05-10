<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Manrope', sans-serif; }
        .display-font { font-family: 'Fraunces', serif; }
    </style>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.12),_transparent_22%),linear-gradient(180deg,_#f7f2e7,_#efe6d6)] text-stone-900">
    <div class="mx-auto flex min-h-screen max-w-6xl items-center px-4 py-10">
        <div class="grid w-full gap-6 lg:grid-cols-[0.95fr_1.05fr]">
            <section class="rounded-[2.2rem] bg-stone-950 px-8 py-10 text-amber-100 shadow-[0_30px_80px_rgba(28,25,23,0.24)]">
                <p class="text-sm uppercase tracking-[0.28em] text-amber-300/80">Internal Access</p>
                <h1 class="display-font mt-4 text-5xl leading-tight">{{ config('app.name') }}</h1>
                <p class="mt-5 max-w-md text-sm leading-7 text-amber-100/75">Panel internal dipakai untuk monitoring kontribusi, sertifikat apresiasi, data PIC, dan verifikasi transfer pada rebuild fase pertama.</p>
                <div class="mt-8 space-y-4">
                    <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                        <p class="font-semibold">Superadmin</p>
                        <p class="mt-1 text-sm text-amber-100/75">Akses dashboard penuh, kontribusi, export, dan operasional.</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                        <p class="font-semibold">PIC</p>
                        <p class="mt-1 text-sm text-amber-100/75">Akses channel performa, daftar kontribusi, dan unduh sertifikat.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-[2.2rem] border border-stone-200 bg-white p-8 shadow-sm">
                <p class="text-sm uppercase tracking-[0.28em] text-stone-500">Login</p>
                <h2 class="mt-3 text-3xl font-bold text-stone-950">Masuk ke workspace internal</h2>

                @if ($errors->any())
                    <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                    @csrf
                    <div>
                        <label for="role" class="mb-2 block text-sm font-semibold text-stone-700">Masuk Sebagai</label>
                        <select id="role" name="role" required class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm">
                            <option value="">Pilih role</option>
                            <option value="SUPERADMIN" {{ old('role') == 'SUPERADMIN' ? 'selected' : '' }}>Superadmin</option>
                            <option value="PIC" {{ old('role') == 'PIC' ? 'selected' : '' }}>PIC</option>
                        </select>
                    </div>
                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-stone-700">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm" placeholder="nama@email.com">
                    </div>
                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-stone-700">Password</label>
                        <input type="password" id="password" name="password" required class="w-full rounded-2xl border border-stone-300 px-4 py-3 text-sm" placeholder="Masukkan password">
                    </div>
                    <label class="flex items-center gap-3 text-sm text-stone-600">
                        <input type="checkbox" id="remember" name="remember" class="h-4 w-4 rounded border-stone-300">
                        Ingat saya
                    </label>
                    <button type="submit" class="w-full rounded-full bg-emerald-900 px-5 py-4 text-sm font-semibold uppercase tracking-[0.22em] text-amber-100">Masuk</button>
                </form>

                <div class="mt-6 rounded-[1.5rem] bg-stone-100 px-4 py-4 text-sm text-stone-600">
                    Akun dibagikan oleh administrator. Hubungi admin bila akses Anda belum aktif.
                </div>

                <a href="{{ route('landing') }}" class="mt-6 inline-flex text-sm font-semibold text-emerald-800"><- Kembali ke beranda</a>
            </section>
        </div>
    </div>
</body>
</html>
