@extends('layouts.public')

@section('title', 'Patungan Sudah Ditutup')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="overflow-hidden rounded-[2.2rem] border border-stone-200 bg-white shadow-sm">
        <div class="bg-gradient-to-r from-stone-950 via-emerald-950 to-amber-700 px-8 py-10 text-amber-50 text-center">
            <p class="text-sm uppercase tracking-[0.28em] text-amber-200/80">Akses Ditutup</p>
            <h2 class="display-font mt-3 text-4xl">Periode kontribusi qurban telah berakhir.</h2>
            <p class="mt-4 text-sm leading-7 text-amber-50/80">Terima kasih atas partisipasi dan dukungan Anda pada program tahun ini.</p>
        </div>
        <div class="px-8 py-8 text-center">
            <div class="mx-auto max-w-lg rounded-[1.75rem] bg-stone-100 px-6 py-5">
                <p class="text-xs uppercase tracking-[0.24em] text-stone-500">Penutupan Resmi</p>
                <p class="mt-2 text-xl font-bold text-stone-950">{{ $closingLabel }}</p>
            </div>
            <p class="mt-6 text-sm leading-7 text-stone-600">Jika Anda membutuhkan konfirmasi kontribusi yang sudah pernah disubmit, gunakan link sertifikat yang telah diterima atau hubungi PIC terkait.</p>
            <a href="{{ route('landing') }}" class="mt-6 inline-flex rounded-full bg-stone-950 px-5 py-3 text-sm font-semibold text-amber-100">Kembali ke Beranda</a>
        </div>
    </div>
</div>
@endsection
