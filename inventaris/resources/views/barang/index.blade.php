<x-app-layout :title="__('Manajemen Inventaris')">
    @php
        $qs = request()->getQueryString() ? '?' . request()->getQueryString() : '';
    @endphp

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Sarana & Prasarana STTNI') }}
            </h2>
            @can('create barang')
                <a href="{{ route('barang.create') }}{{ $qs }}">
                    <x-primary-button>
                        {{ __('Tambah Barang') }}
                    </x-primary-button>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Standard Centered White Card Container -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150">
                <div class="p-6 text-gray-900">
                    
                    <!-- Top Action Bar & Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
                        <!-- Search Box -->
                        <div class="md:col-span-3">
                            <x-input-label for="searchInput" :value="__('Cari Fasilitas / Kode')" />
                            <x-text-input id="searchInput" type="text" value="{{ request('search') }}" class="mt-1 block w-full shadow-sm placeholder-gray-400 focus:border-blue-500 focus:ring-blue-500" placeholder="Cari nama fasilitas, barang, atau kode inventaris..." />
                        </div>

                        <!-- Filter Ruangan (STTNI Categorized Option Group) -->
                        <div>
                            <x-input-label for="filterRuangan" :value="__('Klasifikasi Ruangan')" />
                            <select id="filterRuangan" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
<option value="">{{ __('Semua Ruangan') }}</option>
                
                @php
                    $selectedRuanganId = request()->query('ruangan_id');
                @endphp

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
                                                <option value="{{ $ruangan->id }}" @selected($selectedRuanganId == $ruangan->id)>{{ $ruangan->nama_ruangan }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Kondisi -->
                        <div>
                            <x-input-label for="filterKondisi" :value="__('Status Kondisi')" />
                            <select id="filterKondisi" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                <option value="">{{ __('Semua Kondisi') }}</option>
                                @foreach($kondisis as $kondisi)
                                    <option value="{{ $kondisi }}" @selected(request('kondisi') == $kondisi)>{{ $kondisi }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Kategori -->
                        <div>
                            <x-input-label for="filterKategori" :value="__('Kategori')" />
                            <select id="filterKategori" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm">
                                <option value="">{{ __('Semua Kategori') }}</option>
                                @foreach($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}" @selected(request('kategori_id') == $kategori->id)>{{ $kategori->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-6 flex flex-wrap items-end justify-end gap-2">
                            @can('import barang')
                                <a href="{{ route('barang.import.form') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    {{ __('Import') }}
                                </a>
                            @endcan
                            @can('export barang')
                            <a href="{{ route('barang.export', ['format' => 'csv']) }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('Export Excel/CSV') }}
                            </a>
                            <a href="{{ route('barang.export', ['format' => 'word']) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('Export Word') }}
                            </a>
                            @endcan
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="overflow-x-auto rounded-lg border border-gray-100 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider sticky left-0 z-30 bg-gray-50 border-r border-gray-200 w-56">
                                        Kode Inventaris
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider sticky left-56 z-20 bg-gray-50 border-r border-gray-200">
                                        Nama Fasilitas
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Kategori
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Ruangan / Unit Kerja
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Kondisi
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Keterangan
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Tahun Pembelian
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <!-- Table Body -->
                            <tbody id="tableBody" class="bg-white divide-y divide-gray-100">
                                @include('barang.partials.table')
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Vanilla JS AJAX Debounce Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const filterRuangan = document.getElementById('filterRuangan');
            const filterKondisi = document.getElementById('filterKondisi');
            const filterKategori = document.getElementById('filterKategori');
            const tableBody = document.getElementById('tableBody');

            let debounceTimer;

            // Handle fetching data asynchronously
            function fetchData(url) {
                // Dim table body to indicate loading
                tableBody.style.opacity = '0.6';

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Gagal mengambil data dari server.');
                    }
                    return response.text();
                })
                .then(html => {
                    tableBody.innerHTML = html;
                    tableBody.style.opacity = '1';
                    
                    // Update current browser history state without page reload
                    window.history.pushState({}, '', url);
                })
                .catch(error => {
                    console.error('AJAX Error:', error);
                    tableBody.style.opacity = '1';
                    alert('Terjadi kesalahan saat memuat data.');
                });
            }

            // Consolidate current filters into query string
            function applyFilters() {
                const params = new URLSearchParams();
                
                if (searchInput.value.trim() !== '') {
                    params.append('search', searchInput.value.trim());
                }
                
                if (filterRuangan.value !== '') {
                    params.append('ruangan_id', filterRuangan.value);
                }
                
                if (filterKondisi.value !== '') {
                    params.append('kondisi', filterKondisi.value);
                }

                if (filterKategori.value !== '') {
                    params.append('kategori_id', filterKategori.value);
                }

                const url = `${window.location.pathname}?${params.toString()}`;
                fetchData(url);
            }

            // keyup with 300ms debounce
            searchInput.addEventListener('keyup', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(applyFilters, 300);
            });

            // change events trigger immediately
            filterRuangan.addEventListener('change', applyFilters);
            filterKondisi.addEventListener('change', applyFilters);
            filterKategori.addEventListener('change', applyFilters);

            // AJAX Pagination link handler inside the tableBody container
            tableBody.addEventListener('click', function (e) {
                const link = e.target.closest('.pagination-wrapper a, nav a');
                if (link) {
                    e.preventDefault();
                    const url = link.getAttribute('href');
                    if (url) {
                        fetchData(url);
                    }
                }
            });
        });
    </script>
</x-app-layout>
