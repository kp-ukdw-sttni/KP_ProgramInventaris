<x-app-layout :title="__('Edit Kategori')">
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Kategori Barang STTNI') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-150 p-6">
                <!-- Helper Text -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-150 text-blue-800 rounded-lg text-sm">
                    {{ __('Perbarui nama kategori. Perubahan akan langsung diterapkan pada seluruh barang dalam kategori ini.') }}
                </div>

                <form method="POST" action="{{ route('kategori.update', $kategori->id) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Nama Kategori -->
                    <div>
                        <x-input-label for="nama_kategori" :value="__('Nama Kategori')" />
                        <x-text-input id="nama_kategori" name="nama_kategori" type="text" class="mt-1 block w-full focus:border-blue-500 focus:ring-blue-500" :value="old('nama_kategori', $kategori->nama_kategori)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('nama_kategori')" />
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-4 border-t border-gray-100 pt-4">
                        <a href="{{ route('kategori.index') }}" class="text-sm text-gray-600 hover:text-gray-900 transition">
                            {{ __('Batal') }}
                        </a>
                        <x-primary-button>
                            {{ __('Perbarui Kategori') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>