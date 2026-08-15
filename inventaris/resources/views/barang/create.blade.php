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
                        <!-- Ruangan Multi-Pilih dengan Pengelompokan STTNI -->
                        <div class="md:col-span-2">
                            <x-input-label :value="__('Ruangan / Lokasi Penempatan')" />
                            <p class="mt-1 text-xs text-gray-400">Pilih satu atau lebih ruangan. Barang akan dibuat di setiap ruangan yang dipilih tanpa input berulang.</p>

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

                                $oldRuanganIds = old('ruangan_ids', []);
                            @endphp

                            <div class="mt-2 border border-gray-200 rounded-md bg-gray-50 p-4 max-h-72 overflow-y-auto">
                                <label class="flex items-center space-x-2 text-sm text-gray-600 cursor-pointer hover:text-gray-900 mb-3 border-b border-gray-200 pb-3">
                                    <input type="checkbox" id="selectAllRuangan" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="font-medium">Pilih Semua Ruangan</span>
                                </label>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($groupedRuangan as $groupLabel => $items)
                                        @if(count($items) > 0)
                                            <div>
                                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">{{ $groupLabel }}</p>
                                                <div class="space-y-2">
                                                    @foreach($items as $ruangan)
                                                        <label class="flex items-start space-x-2 text-sm text-gray-700 cursor-pointer hover:text-gray-900 ruangan-check">
                                                            <input type="checkbox" name="ruangan_ids[]" value="{{ $ruangan->id }}" class="ruangan-checkbox mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" {{ in_array($ruangan->id, $oldRuanganIds) ? 'checked' : '' }}>
                                                            <span>{{ $ruangan->nama_ruangan }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <p id="ruanganCount" class="mt-2 text-xs text-blue-600 font-medium"></p>
                            <x-input-error class="mt-2" :messages="$errors->get('ruangan_ids')" />
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
                            <p class="mt-1 text-xs text-gray-400">Setiap unit akan dibuat terpisah dengan kode inventaris uniknya sendiri.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('jumlah')" />
                        </div>

                        <!-- Kode Inventaris (Auto) -->
                        <div class="md:col-span-2">
                            <x-input-label :value="__('Kode Inventaris')" />
                            <div class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-200 text-gray-500 rounded-md text-sm">
                                {{ __('Dibuat otomatis per unit (berdasarkan ruangan)') }}
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Contoh: Meja di Ruang Ketua menghasilkan INV-RUANGKETUA-001, INV-RUANGKETUA-002, dan seterusnya.</p>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAllRuangan');
            const checkboxes = document.querySelectorAll('.ruangan-checkbox');
            const countEl = document.getElementById('ruanganCount');

            function updateCount() {
                const checked = document.querySelectorAll('.ruangan-checkbox:checked').length;
                countEl.textContent = checked > 0
                    ? checked + ' ruangan terpilih — barang akan dibuat di setiap ruangan tersebut.'
                    : '';
                selectAll.checked = checked > 0 && checked === checkboxes.length;
                selectAll.indeterminate = checked > 0 && checked < checkboxes.length;
            }

            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => { cb.checked = selectAll.checked; });
                updateCount();
            });

            checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

            updateCount();
        });
    </script>
</x-app-layout>
