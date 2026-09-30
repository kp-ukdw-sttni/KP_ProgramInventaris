<x-app-layout :title="__('Daftar Kategori')">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Daftar Kategori Barang STTNI') }}
            </h2>
            <a href="{{ route('kategori.create') }}">
                <x-primary-button>
                    {{ __('Tambah Kategori') }}
                </x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Standard Centered White Card Container -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150">
                <div class="p-6 text-gray-900">

                    <!-- Sort Bar -->
                    <form method="GET" action="{{ route('kategori.index') }}" class="mb-6 flex flex-wrap items-end gap-4">
                        <div>
                            <x-input-label for="sort" :value="__('Urutkan Berdasarkan')" />
                            <select name="sort" id="sort" onchange="this.form.submit()" class="mt-1 block border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                <option value="nama_kategori" @selected($column === 'nama_kategori')>{{ __('Nama Kategori') }}</option>
                                <option value="id" @selected($column === 'id')>{{ __('Kode Kategori') }}</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="direction" :value="__('Arah Pengurutan')" />
                            <select name="direction" id="direction" onchange="this.form.submit()" class="mt-1 block border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                <option value="asc" @selected($direction === 'asc')>{{ __('Ascending (A-Z)') }}</option>
                                <option value="desc" @selected($direction === 'desc')>{{ __('Descending (Z-A)') }}</option>
                            </select>
                        </div>
                    </form>

                    <div class="overflow-x-auto rounded-lg border border-gray-100 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Kode
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Nama Kategori
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Jumlah Aset
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($kategoris as $kategori)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-500">
                                            {{ str_pad($kategori->id, 3, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ $kategori->nama_kategori }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-800">
                                            <a href="{{ route('kategori.show', $kategori->id) }}"
                                               title="Lihat rincian jenis barang per kategori"
                                               class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-900 hover:underline transition">
                                                {{ $kategori->barangs_count }} barang
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12" />
                                                </svg>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                            <div class="inline-flex items-center space-x-2">
                                                <a href="{{ route('kategori.edit', $kategori->id) }}" class="inline-flex items-center text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-2 py-1 rounded-md transition-colors">
                                                    Edit
                                                </a>
                                                <form action="{{ route('kategori.destroy', $kategori->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini? Barang dengan kategori ini akan menjadi tanpa kategori.');" class="inline">
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
                                        <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                                            Tidak ada kategori terdaftar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $kategoris->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>