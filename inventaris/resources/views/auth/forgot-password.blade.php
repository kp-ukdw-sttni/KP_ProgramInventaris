<x-guest-layout :title="__('Lupa Kata Sandi')">
    <h1 class="mb-4 text-center text-lg font-semibold text-gray-700">
        {{ __('Lupa Kata Sandi?') }}
    </h1>

    <div class="mb-4 text-sm text-gray-600">
        {{ __('Tidak masalah. Masukkan alamat email Anda dan kami akan mengirim tautan untuk mengatur kata sandi yang baru.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Kirim Tautan Atur Ulang Kata Sandi') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
