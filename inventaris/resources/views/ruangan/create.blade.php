<x-app-layout :title="__('Tambah Ruangan')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Ruangan / Unit Kerja STTNI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150 p-6">
                <!-- Helper Text -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-150 text-blue-800 rounded-lg text-sm">
                    {{ __('Daftarkan ruangan akademik baru, kantor program studi, unit rektorat pimpinan, atau gedung fasilitas umum STTNI.') }}
                </div>

                <form method="POST" action="{{ route('ruangan.store') }}" class="space-y-6">
                    @csrf

                    <!-- Nama Ruangan -->
                    <div>
                        <x-input-label for="nama_ruangan" :value="__('Nama Ruangan / Unit Kerja')" />
                        <x-text-input id="nama_ruangan" name="nama_ruangan" type="text" class="mt-1 block w-full focus:border-blue-500 focus:ring-blue-500" :value="old('nama_ruangan')" required autofocus placeholder="Contoh: Ruang WK III Bidang Kemahasiswaan" />
                        <x-input-error class="mt-2" :messages="$errors->get('nama_ruangan')" />
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <x-input-label for="deskripsi" :value="__('Deskripsi / Fungsi Ruangan')" />
                        <textarea id="deskripsi" name="deskripsi" class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm" rows="4" placeholder="Fungsi ruangan, kapasitas, atau penanggung jawab ruangan...">{{ old('deskripsi') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('deskripsi')" />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-4 border-t border-gray-100 pt-4">
                        <a href="{{ route('ruangan.index') }}" class="text-sm text-gray-600 hover:text-gray-900 transition">
                            {{ __('Batal') }}
                        </a>
                        <x-primary-button>
                            {{ __('Simpan Ruangan') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
