<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Barang Inventaris STTNI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150 p-6">
                <!-- Helper Text banner -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-150 text-blue-800 rounded-lg text-sm flex items-start space-x-2">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('Tambahkan fasilitas baru ke dalam ruangan prodi atau unit kerja terkait di Sekolah Tinggi Theologia Nazarene Indonesia.') }}</span>
                </div>

                <form method="POST" action="{{ route('barang.store') }}" class="space-y-6">
                    @csrf

                    <!-- Nama Fasilitas -->
                    <div>
                        <x-input-label for="nama_fasilitas" :value="__('Nama Fasilitas / Barang')" />
                        <x-text-input id="nama_fasilitas" name="nama_fasilitas" type="text" class="mt-1 block w-full focus:border-blue-500 focus:ring-blue-500" :value="old('nama_fasilitas')" required autofocus placeholder="Contoh: Laptop Acer Core i3" />
                        <x-input-error class="mt-2" :messages="$errors->get('nama_fasilitas')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Ruangan dengan Pengelompokan STTNI -->
                        <div>
                            <x-input-label for="ruangan_id" :value="__('Ruangan / Lokasi Penempatan')" />
                            <select id="ruangan_id" name="ruangan_id" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" required>
                                <option value="">{{ __('Pilih Ruangan') }}</option>
                                
                                @php
                                    $groupedRuangan = [
                                        'Area Akademik & Kelas' => [],
                                        'Area Program Studi & Pasca Sarjana' => [],
                                        'Area Pimpinan & Rektorat' => [],
                                        'Fasilitas Umum & Mahasiswa' => [],
                                    ];

                                    foreach($ruangans as $ruangan) {
                                        $nama = $ruangan->nama_ruangan;
                                        if (Illuminate\Support\Str::contains($nama, ['Kelas A', 'Kelas B', 'Kelas C', 'Kelas D', 'Kelas E', 'Laboratorium'])) {
                                            $groupedRuangan['Area Akademik & Kelas'][] = $ruangan;
                                        } elseif (Illuminate\Support\Str::contains($nama, ['Kaprodi', 'Sekretaris Prodi', 'Pasca Sarjana'])) {
                                            $groupedRuangan['Area Program Studi & Pasca Sarjana'][] = $ruangan;
                                        } elseif (Illuminate\Support\Str::contains($nama, ['Ketua', 'WK', 'Bendahara', 'Sekretaris Umum'])) {
                                            $groupedRuangan['Area Pimpinan & Rektorat'][] = $ruangan;
                                        } else {
                                            $groupedRuangan['Fasilitas Umum & Mahasiswa'][] = $ruangan;
                                        }
                                    }
                                @endphp

                                @foreach($groupedRuangan as $groupLabel => $items)
                                    @if(count($items) > 0)
                                        <optgroup label="{{ $groupLabel }}">
                                            @foreach($items as $ruangan)
                                                <option value="{{ $ruangan->id }}" {{ old('ruangan_id') == $ruangan->id ? 'selected' : '' }}>{{ $ruangan->nama_ruangan }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('ruangan_id')" />
                        </div>

                        <!-- Kategori -->
                        <div>
                            <x-input-label for="kategori_id" :value="__('Kategori Barang')" />
                            <select id="kategori_id" name="kategori_id" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                <option value="">{{ __('Pilih Kategori (Opsional)') }}</option>
                                @foreach($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>{{ $kategori->nama_kategori }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('kategori_id')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Jumlah -->
                        <div>
                            <x-input-label for="jumlah" :value="__('Jumlah Unit')" />
                            <x-text-input id="jumlah" name="jumlah" type="number" class="mt-1 block w-full focus:border-blue-500 focus:ring-blue-500" :value="old('jumlah', 1)" min="1" required />
                            <x-input-error class="mt-2" :messages="$errors->get('jumlah')" />
                        </div>

                        <!-- Kode Inventaris -->
                        <div class="md:col-span-2">
                            <x-input-label for="kode_inventaris" :value="__('Kode Inventaris Unik')" />
                            <x-text-input id="kode_inventaris" name="kode_inventaris" type="text" class="mt-1 block w-full focus:border-blue-500 focus:ring-blue-500" :value="old('kode_inventaris')" placeholder="Contoh: INV-KAPEL-001" />
                            <x-input-error class="mt-2" :messages="$errors->get('kode_inventaris')" />
                        </div>
                    </div>

                    <!-- Kondisi -->
                    <div>
                        <x-input-label for="kondisi" :value="__('Status Kondisi Fisik')" />
                        <select id="kondisi" name="kondisi" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" required>
                            <option value="">{{ __('Pilih Kondisi') }}</option>
                            @foreach($kondisis as $kondisi)
                                <option value="{{ $kondisi }}" {{ old('kondisi') == $kondisi ? 'selected' : '' }}>{{ $kondisi }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('kondisi')" />
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <x-input-label for="keterangan" :value="__('Keterangan / Deskripsi Tambahan')" />
                        <textarea id="keterangan" name="keterangan" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" rows="3" placeholder="Tambahkan deskripsi fisik, nomor seri, catatan kerusakan, dll.">{{ old('keterangan') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('keterangan')" />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-4 border-t border-gray-100 pt-4">
                        <a href="{{ route('barang.index') }}" class="text-sm text-gray-600 hover:text-gray-900 transition">
                            {{ __('Batal') }}
                        </a>
                        <x-primary-button>
                            {{ __('Simpan Barang') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
