@extends('layouts.admin')

@section('title', 'Kelola PIC')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-emerald-950">Kelola PIC</h2>
            <p class="text-emerald-600 text-sm">Person In Charge — Kasie mengontrol komunitas melalui PIC Komunitas</p>
        </div>
        <a href="{{ route('admin.pics.create') }}"
            class="rounded-full bg-emerald-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800 transition">
            + Tambah PIC
        </a>
    </div>

    <div class="rounded-[1.8rem] bg-white border border-emerald-100 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-emerald-100 text-sm">
            <thead class="bg-emerald-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Level</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Bawahan / Komunitas</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-emerald-600">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-emerald-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-emerald-50">
                @forelse($pics as $pic)
                    <tr class="hover:bg-emerald-50/50">
                        <td class="px-6 py-4 font-semibold text-emerald-950">{{ $pic->name }}</td>
                        <td class="px-6 py-4">
                            @if($pic->isKomunitas())
                                <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-full bg-sky-100 text-sky-800">Komunitas</span>
                            @else
                                <span class="inline-flex px-2.5 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800">Kasie</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-mono text-stone-500 text-xs">{{ $pic->code ?? '-' }}</td>
                        <td class="px-6 py-4">
                            @if($pic->isKomunitas())
                                @if($pic->communityAsPicKomunitas)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 border border-sky-200 px-2.5 py-1 text-xs font-medium text-sky-800">
                                        {{ $pic->communityAsPicKomunitas->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-stone-400 italic">Belum ada komunitas</span>
                                @endif
                            @else
                                {{-- Kasie: tampilkan PIC Komunitas bawahannya --}}
                                @php $subs = $pic->communities->filter(fn($c) => $c->picKomunitas !== null) @endphp
                                @if($subs->isNotEmpty())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($subs as $c)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 border border-amber-200 px-2 py-0.5 text-xs font-medium text-amber-800">
                                                {{ $c->picKomunitas->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-stone-400 italic">Belum ada bawahan</span>
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($pic->is_active)
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                            @else
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-stone-100 text-stone-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <a href="{{ route('admin.pics.show', $pic) }}" class="text-emerald-600 hover:text-emerald-900 font-medium">Detail</a>
                            <a href="{{ route('admin.pics.edit', $pic) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                            <form action="{{ route('admin.pics.destroy', $pic) }}" method="POST" class="inline"
                                onsubmit="return confirm('Yakin ingin menghapus PIC ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-emerald-400">
                            Belum ada PIC.
                            <a href="{{ route('admin.pics.create') }}" class="text-emerald-700 underline">Tambah PIC pertama</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($pics->hasPages())
        <div>{{ $pics->links() }}</div>
    @endif
</div>
@endsection
