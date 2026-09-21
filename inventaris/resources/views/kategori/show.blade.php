<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Aset Kategori') }}
            </h2>
            <a href="{{ route('kategori.index') }}">
                <x-secondary-button>
                    {{ __('Kembali ke Daftar Kategori') }}
                </x-secondary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <!-- Category Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150 p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $kategori->nama_kategori }}</h3>
                        <p class="mt-2 text-gray-500">Kode Kategori: <span class="font-semibold text-gray-700">{{ str_pad($kategori->id, 3, '0', STR_PAD_LEFT) }}</span></p>
                    </div>
                    <a href="{{ route('barang.index', ['kategori_id' => $kategori->id]) }}"
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

            <!-- Per-Nama-Barang Breakdown -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150">
                <div class="p-6 text-gray-900">
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Rincian Barang per Jenis</h4>

                    <div class="overflow-x-auto rounded-lg border border-gray-100 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Jenis Barang
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Jumlah
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($perNamaBarang as $nama => $jumlah)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $nama }}</td>
                                        <td class="px-6 py-4 text-center text-sm font-bold text-gray-800">{{ $jumlah }} barang</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-6 py-10 text-center text-sm text-gray-500">
                                            Belum ada barang terdaftar pada kategori ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(count($perNamaBarang) > 0)
                                <tfoot class="bg-gray-50 border-t border-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 text-sm font-semibold text-gray-600 uppercase tracking-wider">Total</td>
                                        <td class="px-6 py-4 text-center text-sm font-bold text-gray-800">{{ $totalBarang }} barang</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>