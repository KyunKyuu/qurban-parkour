@extends('layouts.public')

@section('title', 'Kode Kontribusi Tidak Valid')

@section('content')
<div class="mx-auto max-w-xl">
    <div class="rounded-[2rem] border border-red-200 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-50 text-4xl">!</div>
        <h2 class="display-font mt-5 text-3xl text-stone-950">Kode kontribusi tidak bisa dipakai</h2>
        <p class="mt-3 text-sm leading-7 text-stone-600">Sistem tidak dapat memproses kode berikut untuk flow kurban saat ini.</p>

        <div class="mt-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-4 text-left text-sm text-red-700">
            {{ $error }}
        </div>

        <div class="mt-5 rounded-2xl bg-stone-100 px-4 py-4">
            <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Kode yang dicek</p>
            <p class="mt-2 font-mono text-lg font-bold text-stone-950">{{ $code }}</p>
        </div>

        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('public.contribute') }}" class="rounded-full bg-stone-950 px-5 py-3 text-sm font-semibold text-amber-100">Buka Form Direct Web</a>
            <a href="{{ route('landing') }}" class="rounded-full border border-stone-300 px-5 py-3 text-sm font-semibold text-stone-700">Kembali ke Beranda</a>
        </div>
    </div>
</div>
@endsection
