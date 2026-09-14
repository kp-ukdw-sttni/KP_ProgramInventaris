<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Aset Ruangan') }}
            </h2>
            <a href="{{ route('ruangan.index') }}">
                <x-secondary-button>
                    {{ __('Kembali ke Daftar Ruangan') }}
                </x-secondary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- Room Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150 p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $ruangan->nama_ruangan }}</h3>
                        @if($ruangan->deskripsi)
                            <p class="mt-2 text-gray-500">{{ $ruangan->deskripsi }}</p>
                        @endif
                    </div>
                    <a href="{{ route('barang.index', ['ruangan_id' => $ruangan->id]) }}"
                       class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-md text-sm font-semibold hover:bg-blue-100 transition-colors whitespace-nowrap">
                        Lihat Daftar Barang
                    </a>
                </div>

                <div class="mt-5 flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg">
                    <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wider">Total Aset Terdaftar</div>
                        <div class="text-2xl font-bold leading-tight">{{ $totalBarang }} <span class="text-sm font-medium">barang</span></div>
                    </div>
                </div>
            </div>

            <!-- Per-Kategori Breakdown -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150 mb-6">
                <div class="p-6 text-gray-900">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Rincian Barang per Kategori</h4>

                    @forelse($perKategori as $item)
                        @php
                            $persen = $totalBarang > 0 ? round(($item->total / $totalBarang) * 100) : 0;
                        @endphp
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $item->kategoriBarang->nama_kategori ?? 'Tanpa Kategori' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="w-32 sm:w-56 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-2 bg-blue-500 rounded-full" style="width: {{ $persen }}%"></div>
                                </div>
                                <div class="w-16 text-right">
                                    <span class="text-sm font-bold text-gray-800">{{ $item->total }} barang</span>
                                </div>
                                <div class="w-12 text-right text-sm text-gray-500">{{ $persen }}%</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-8">
                            Belum ada barang terdaftar di ruangan ini.
                        </p>
                    @endforelse

                    @if(count($perKategori) > 0)
                        <div class="flex items-center justify-between pt-4">
                            <span class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total</span>
                            <span class="text-sm font-bold text-gray-800">{{ $totalBarang }} barang</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Per-Nama-Barang Breakdown -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150">
                <div class="p-6 text-gray-900">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Rincian Barang per Jenis</h4>

                    @forelse($perNamaBarang as $nama => $jumlah)
                        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                            <span class="text-sm font-semibold text-gray-800">{{ $nama }}</span>
                            <span class="text-sm font-bold text-gray-800">{{ $jumlah }} barang</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-8">
                            Belum ada barang terdaftar di ruangan ini.
                        </p>
                    @endforelse

                    @if(count($perNamaBarang) > 0)
                        <div class="flex items-center justify-between pt-4">
                            <span class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total</span>
                            <span class="text-sm font-bold text-gray-800">{{ $totalBarang }} barang</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>