@extends('layouts.public')

@section('title', 'Sesi Claim Voucher Telah Berakhir')

@section('content')
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }
    @keyframes glow {
        0%, 100% { box-shadow: 0 0 20px rgba(251, 191, 36, 0.3); }
        50% { box-shadow: 0 0 40px rgba(251, 191, 36, 0.6); }
    }
    .float-animation {
        animation: float 3s ease-in-out infinite;
    }
    .glow-animation {
        animation: glow 2s ease-in-out infinite;
    }
</style>

<div class="max-w-lg mx-auto">
    <!-- Main Card -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden glow-animation">
        <!-- Header with Ramadhan Theme -->
        <div class="bg-gradient-to-br from-amber-400 via-orange-500 to-amber-600 p-8 text-white text-center relative overflow-hidden">
            <div class="relative z-10">
                <div class="text-7xl mb-4 float-animation">🌙</div>
                <h2 class="text-3xl font-bold mb-2">Maaf, Sesi Claim Voucher Telah Berakhir</h2>
                <div class="w-24 h-1 bg-white/50 mx-auto rounded-full my-4"></div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-8 text-center">
            <!-- Subtitle -->
            <p class="text-xl text-gray-700 mb-6 font-medium">
                Terima kasih atas partisipasi Anda. Sampai jumpa di Ramadhan berikutnya!
            </p>

            <!-- Decorative Divider -->
            <div class="flex items-center justify-center mb-6">
                <div class="h-px bg-gradient-to-r from-transparent via-amber-400 to-transparent w-full"></div>
                <span class="px-4 text-amber-500 text-2xl">✨</span>
                <div class="h-px bg-gradient-to-r from-transparent via-amber-400 to-transparent w-full"></div>
            </div>

            <!-- Islamic Pattern/Quote -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl p-6 mb-6 border border-amber-200">
                <p class="text-lg text-gray-800 italic mb-3">
                    "Ramadhan adalah bulan yang penuh berkah. Semoga amal ibadah kita diterima oleh Allah SWT."
                </p>
                <p class="text-sm text-amber-700 font-semibold">- عتقكم من الله -</p>
            </div>

            <!-- Additional Message -->
            <div class="space-y-3 text-gray-600">
                <p class="flex items-center justify-center gap-2">
                    <span class="text-amber-500">✦</span>
                    <span>Terima kasih telah berbagi kebahagiaan</span>
                    <span class="text-amber-500">✦</span>
                </p>
                <p class="flex items-center justify-center gap-2">
                    <span class="text-amber-500">✦</span>
                    <span>Semoga bermanfaat untuk yang membutuhkan</span>
                    <span class="text-amber-500">✦</span>
                </p>
                <p class="flex items-center justify-center gap-2">
                    <span class="text-amber-500">✦</span>
                    <span>Mohon maaf lahir dan batin</span>
                    <span class="text-amber-500">✦</span>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gradient-to-r from-amber-400 via-orange-500 to-amber-600 py-4 text-white text-center">
            <p class="text-sm font-medium">تقبل الله منا ومنكم</p>
            <p class="text-xs text-amber-100 mt-1">Semoga Allah menerima amal kita semua</p>
        </div>
    </div>
</div>
@endsection
