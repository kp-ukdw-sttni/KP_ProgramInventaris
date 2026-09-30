<x-app-layout :title="__('Daftar Ruangan')">
    @php
        $qs = request()->getQueryString() ? '?' . request()->getQueryString() : '';
    @endphp

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Ruangan & Unit Kerja STTNI') }}
            </h2>
            <a href="{{ route('ruangan.create') }}{{ $qs }}">
                <x-primary-button>
                    {{ __('Tambah Ruangan') }}
                </x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Standard Centered White Card Container -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150">
                <div class="p-6 text-gray-900">
                    
                    <div class="overflow-x-auto rounded-lg border border-gray-100 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-3 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Urutan
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Klasifikasi Area
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Nama Ruangan / Unit Kerja
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Deskripsi Ruangan
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Jumlah Aset Terdaftar
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($ruangans as $ruangan)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <!-- Reorder controls -->
                                        <td class="px-3 py-4 whitespace-nowrap text-center align-middle">
                                            @php
                                                $bolehNaik = ! $loop->first;
                                                $bolehTurun = ! $loop->last;
                                            @endphp
                                            <div class="inline-flex items-center gap-1">
                                                <form action="{{ route('ruangan.move', $ruangan->id) }}{{ $qs }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="up">
                                                    <button type="submit" title="Naikkan urutan" aria-label="Naikkan urutan {{ $ruangan->nama_ruangan }}"
                                                            @disabled(! $bolehNaik)
                                                            class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-500 transition-colors hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 disabled:cursor-not-allowed disabled:border-gray-100 disabled:bg-gray-50 disabled:text-gray-300">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                                        </svg>
                                                    </button>
                                                </form>
                                                <form action="{{ route('ruangan.move', $ruangan->id) }}{{ $qs }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="down">
                                                    <button type="submit" title="Turunkan urutan" aria-label="Turunkan urutan {{ $ruangan->nama_ruangan }}"
                                                            @disabled(! $bolehTurun)
                                                            class="inline-flex h-7 w-7 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-500 transition-colors hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 disabled:cursor-not-allowed disabled:border-gray-100 disabled:bg-gray-50 disabled:text-gray-300">
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <!-- Dynamic Area Classification Badges -->
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @php
                                                $nama = $ruangan->nama_ruangan;
                                                if (Illuminate\Support\Str::contains($nama, ['Kelas A', 'Kelas B', 'Kelas C', 'Kelas D', 'Kelas E', 'Laboratorium'])) {
                                                    $badgeColor = 'bg-blue-50 text-blue-700 border border-blue-200';
                                                    $areaName = 'Akademik & Kelas';
                                                } elseif (Illuminate\Support\Str::contains($nama, ['Kaprodi', 'Sekretaris Prodi', 'Pasca Sarjana'])) {
                                                    $badgeColor = 'bg-purple-50 text-purple-700 border border-purple-200';
                                                    $areaName = 'Program Studi';
                                                } elseif (Illuminate\Support\Str::contains($nama, ['Ketua', 'WK', 'Bendahara', 'Sekretaris Umum'])) {
                                                    $badgeColor = 'bg-amber-50 text-amber-700 border border-amber-200';
                                                    $areaName = 'Pimpinan & Rektorat';
                                                } else {
                                                    $badgeColor = 'bg-gray-50 text-gray-700 border border-gray-200';
                                                    $areaName = 'Fasilitas Umum';
                                                }
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badgeColor }}">
                                                {{ $areaName }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ $ruangan->nama_ruangan }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-sm truncate" title="{{ $ruangan->deskripsi }}">
                                            {{ $ruangan->deskripsi ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-800">
                                            <a href="{{ route('ruangan.show', $ruangan->id) }}"
                                               title="Lihat rincian aset per kategori"
                                               class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-900 hover:underline transition">
                                                {{ $ruangan->barangs_count }} barang
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12" />
                                                </svg>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                            <div class="inline-flex items-center space-x-2">
                                                <a href="{{ route('ruangan.edit', $ruangan->id) }}{{ $qs }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded-md transition-colors">
                                                    Edit
                                                </a>
                                                <form action="{{ route('ruangan.destroy', $ruangan->id) }}{{ $qs }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan ini? Semua barang di ruangan ini juga akan dihapus.');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center text-rose-600 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 px-2 py-1 rounded-md transition-colors">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500">
                                            Tidak ada ruangan terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $ruangans->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
