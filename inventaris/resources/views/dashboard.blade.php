<x-app-layout :title="__('Dasbor')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dasbor Inventaris Kampus STTNI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Selamat Datang Box -->
            <div class="bg-gradient-to-r from-blue-700 to-blue-600 rounded-2xl shadow-md p-6 text-white mb-8 border border-blue-500">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-white/10 rounded-xl">
                        <!-- Icon School -->
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.168.477 4 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4 1.253" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">Selamat Datang di Sistem Manajemen Sarana & Prasarana</h3>
                        <p class="text-blue-100 text-sm mt-1">Sekolah Tinggi Theologia Nazarene Indonesia (STTNI)</p>
                    </div>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Aset -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-150 p-6 flex items-center justify-between hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Aset / Item</p>
                        <h4 class="text-3xl font-extrabold text-blue-600 mt-2">{{ $totalBarang }}</h4>
                        <p class="text-xs text-gray-400 mt-1">Seluruh unit ber-kode</p>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>

                <!-- Total Ruangan -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-150 p-6 flex items-center justify-between hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Ruangan / Unit Kerja</p>
                        <h4 class="text-3xl font-extrabold text-blue-600 mt-2">{{ $totalRuangan }}</h4>
                        <p class="text-xs text-gray-400 mt-1">Lokasi penempatan</p>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>

                <!-- Kondisi Baik -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-150 p-6 flex items-center justify-between hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Aset Kondisi Baik</p>
                        <h4 class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $kondisiBaik }}</h4>
                        <p class="text-xs text-gray-400 mt-1">{{ $persentaseBaik }}% dari total aset</p>
                    </div>
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <!-- Kondisi Rusak / Mati -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-150 p-6 flex items-center justify-between hover:shadow-md transition-shadow">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Aset Rusak / Mati</p>
                        <h4 class="text-3xl font-extrabold text-rose-600 mt-2">{{ $kondisiRusak }}</h4>
                        <p class="text-xs text-gray-400 mt-1">{{ $persentaseRusak }}% dari total aset</p>
                    </div>
                    <div class="p-3 bg-rose-50 text-rose-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Kondisi -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-150 p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">Ringkasan Status Kondisi</h3>
                    <div class="space-y-4">
                        @php
                            $barColors = [
                                'Baik' => 'bg-emerald-500',
                                'Kurang Baik' => 'bg-amber-500',
                                'Rusak' => 'bg-orange-500',
                                'Mati' => 'bg-rose-500',
                            ];
                            $dotColors = [
                                'Baik' => 'bg-emerald-500',
                                'Kurang Baik' => 'bg-amber-500',
                                'Rusak' => 'bg-orange-500',
                                'Mati' => 'bg-rose-500',
                            ];
                        @endphp
                        @foreach($kondisis as $kondisi)
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="inline-flex items-center text-gray-700 font-medium">
                                        <span class="w-2 h-2 mr-2 rounded-full {{ $dotColors[$kondisi] }}"></span>
                                        {{ $kondisi }}
                                    </span>
                                    <span class="text-gray-500">
                                        {{ $kondisiCounts[$kondisi] ?? 0 }} unit ({{ $kondisiPercentages[$kondisi] }}%)
                                    </span>
                                </div>
                                <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full {{ $barColors[$kondisi] }} rounded-full transition-all" style="width: {{ min(100, $kondisiPercentages[$kondisi]) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 p-4 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-600">Tingkat Kelayakan (Baik + Kurang Baik)</span>
                            <span class="text-sm font-bold text-blue-600">{{ $persentaseLayak }}%</span>
                        </div>
                        <div class="w-full h-2.5 bg-white rounded-full overflow-hidden mt-2 border border-gray-200">
                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ min(100, $persentaseLayak) }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Distribusi per Kategori -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-150 p-6">
                    <h3 class="text-lg font-bold text-gray-700 mb-4">Distribusi Aset per Kategori</h3>
                    <div class="space-y-4">
                        @forelse($kategoris as $kategori)
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-gray-700 font-medium">{{ $kategori['nama'] }}</span>
                                    <span class="text-gray-500">{{ $kategori['total'] }} unit ({{ $kategori['persentase'] }}%)</span>
                                </div>
                                <div class="w-full h-2.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-indigo-500 rounded-full transition-all" style="width: {{ min(100, $kategori['persentase']) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400">Belum ada kategori aset terdaftar.</p>
                        @endforelse
                    </div>

                    <div class="mt-6 flex items-center justify-between p-4 rounded-lg bg-blue-50 border border-blue-100">
                        <span class="text-sm font-semibold text-blue-800">Total seluruh unit aset</span>
                        <span class="text-sm font-bold text-blue-700">{{ $totalBarang }} unit</span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Cards -->
            <h3 class="text-lg font-bold text-gray-700 mb-4">Akses Cepat Layanan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <!-- Inventaris Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-150 p-6 hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <h4 class="text-md font-bold text-gray-800">Manajemen Inventaris</h4>
                        <p class="text-sm text-gray-500 mt-2">Lihat, cari, saring, dan kelola semua sarana & prasarana (barang) kampus STTNI secara dinamis.</p>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('barang.index') }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                            Buka Inventaris
                            <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Ruangan Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-150 p-6 hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <h4 class="text-md font-bold text-gray-800">Daftar Ruangan</h4>
                        <p class="text-sm text-gray-500 mt-2">Daftar klasifikasi ruangan akademik, program studi, pascasarjana, rektorat, dan fasilitas umum STTNI.</p>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('ruangan.index') }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                            Buka Daftar Ruangan
                            <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Kategori Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-150 p-6 hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <h4 class="text-md font-bold text-gray-800">Daftar Kategori</h4>
                        <p class="text-sm text-gray-500 mt-2">Kelola klasifikasi barang seperti kursi, meja, AC, dan komputer agar inventaris lebih rapi.</p>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('kategori.index') }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                            Buka Daftar Kategori
                            <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Tambah Aset Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-150 p-6 hover:shadow-md transition-shadow flex flex-col justify-between">
                    <div>
                        <h4 class="text-md font-bold text-gray-800">Tambah Fasilitas / Barang</h4>
                        <p class="text-sm text-gray-500 mt-2">Daftarkan aset sarpras baru langsung ke prodi, unit kerja, atau fasilitas umum terkait di STTNI.</p>
                    </div>
                    <div class="mt-6">
                        <a href="{{ route('barang.create') }}" class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700 transition">
                            Tambah Aset Baru
                            <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
