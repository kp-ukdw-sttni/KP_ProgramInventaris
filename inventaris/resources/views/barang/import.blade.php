<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Import Barang') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ __('Import data barang dari file CSV yang diekspor sebelumnya.') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('barang.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Kembali') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                    <p class="font-medium whitespace-pre-line">{!! nl2br(e(session('error'))) !!}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
                    <p class="font-medium">{{ __('Terdapat error pada input:') }}</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="space-y-6">
                        <!-- Petunjuk -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h3 class="text-sm font-semibold text-blue-800 mb-2">{{ __('Petunjuk Import') }}</h3>
                            <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                                <li>{{ __('Gunakan file CSV yang diunduh melalui tombol Export (format CSV).') }}</li>
                                <li>{{ __('Kolom yang dibutuhkan: nama_fasilitas, ruangan, kondisi. Opsional: kategori, tahun_pembelian, keterangan.') }}</li>
                                <li>{{ __('Kode inventaris boleh dikosongkan. Sistem akan membuat kode otomatis sesuai format ruangan.') }}</li>
                                <li>{{ __('Pastikan ruangan dan kategori yang digunakan sudah terdaftar di sistem (sesuai nama).') }}</li>
                                <li>{{ __('Sistem akan melakukan validasi sebelum menyimpan data.') }}</li>
                            </ul>
                        </div>

                        <!-- Panduan Kolom -->
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                <h3 class="text-sm font-semibold text-gray-700">{{ __('Panduan Pengisian Kolom') }}</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-white">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-500">{{ __('Kolom') }}</th>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-500">{{ __('Wajib') }}</th>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-500">{{ __('Contoh') }}</th>
                                            <th class="px-4 py-2 text-left font-semibold text-gray-500">{{ __('Keterangan') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-gray-800">kode_inventaris</td>
                                            <td class="px-4 py-2"><span class="text-gray-500">{{ __('Tidak') }}</span></td>
                                            <td class="px-4 py-2 text-gray-600">INV-RUANGKETUA-001</td>
                                            <td class="px-4 py-2 text-gray-600">{{ __('Kosongkan bila ingin kode dibuat otomatis oleh sistem.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-gray-800">nama_fasilitas</td>
                                            <td class="px-4 py-2"><span class="text-red-600 font-semibold">{{ __('Ya') }}</span></td>
                                            <td class="px-4 py-2 text-gray-600">Meja Kerja Eksekutif</td>
                                            <td class="px-4 py-2 text-gray-600">{{ __('Nama barang/fasilitas.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-gray-800">ruangan</td>
                                            <td class="px-4 py-2"><span class="text-red-600 font-semibold">{{ __('Ya') }}</span></td>
                                            <td class="px-4 py-2 text-gray-600">Ruang Ketua</td>
                                            <td class="px-4 py-2 text-gray-600">{{ __('Harus persis sama dengan nama ruangan di sistem.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-gray-800">kategori</td>
                                            <td class="px-4 py-2"><span class="text-gray-500">{{ __('Tidak') }}</span></td>
                                            <td class="px-4 py-2 text-gray-600">Mebel &amp; Interior</td>
                                            <td class="px-4 py-2 text-gray-600">{{ __('Kosongkan bila belum ada kategori.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-gray-800">kondisi</td>
                                            <td class="px-4 py-2"><span class="text-red-600 font-semibold">{{ __('Ya') }}</span></td>
                                            <td class="px-4 py-2 text-gray-600">Baik</td>
                                            <td class="px-4 py-2 text-gray-600">{{ __('Pilih salah satu: Baik, Kurang Baik, Rusak, Mati.') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-gray-800">tahun_pembelian</td>
                                            <td class="px-4 py-2"><span class="text-gray-500">{{ __('Tidak') }}</span></td>
                                            <td class="px-4 py-2 text-gray-600">2020</td>
                                            <td class="px-4 py-2 text-gray-600">{{ __('Format 4 digit tahun (1900-2100).') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-gray-800">keterangan</td>
                                            <td class="px-4 py-2"><span class="text-gray-500">{{ __('Tidak') }}</span></td>
                                            <td class="px-4 py-2 text-gray-600">{{ __('Meja kayu jati.') }}</td>
                                            <td class="px-4 py-2 text-gray-600">{{ __('Catatan tambahan, bebas.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Form Import -->
                        <form action="{{ route('barang.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <x-input-label for="file" :value="__('Pilih File CSV')" />
                                <input type="file" id="file" name="file" accept=".csv,text/csv" required
                                    class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" />
                                <x-input-error :messages="$errors->get('file')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="import_mode" :value="__('Mode Import')" />
                                <select id="import_mode" name="import_mode" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                    <option value="skip">{{ __('Lewati (Skip) jika data sudah ada') }}</option>
                                    @can('replace barang')
                                        <option value="replace">{{ __('Ganti (Replace) data yang sudah ada') }}</option>
                                    @endcan
                                </select>
                                <p class="mt-1 text-xs text-gray-500">{{ __('Skip: data dengan kode inventaris yang sama akan dilewati. Replace: data dengan kode inventaris yang sama akan diperbarui, data baru tetap ditambahkan.') }}</p>
                                <x-input-error :messages="$errors->get('import_mode')" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-3">
                                <x-primary-button>
                                    {{ __('Import Data') }}
                                </x-primary-button>
                                <a href="{{ route('barang.import.template') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ __('Download Template Kosong') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>