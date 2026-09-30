@php
    $durasi = 7000;

    $gaya = [
        'success' => [
            'kartu' => 'border-emerald-200',
            'ikon' => 'bg-emerald-100 text-emerald-600',
            'bar' => 'bg-emerald-500',
        ],
        'error' => [
            'kartu' => 'border-rose-200',
            'ikon' => 'bg-rose-100 text-rose-600',
            'bar' => 'bg-rose-500',
        ],
        'warning' => [
            'kartu' => 'border-amber-200',
            'ikon' => 'bg-amber-100 text-amber-600',
            'bar' => 'bg-amber-500',
        ],
        'info' => [
            'kartu' => 'border-blue-200',
            'ikon' => 'bg-blue-100 text-blue-600',
            'bar' => 'bg-blue-500',
        ],
    ];

    $toasts = [];

    foreach (array_keys($gaya) as $tipe) {
        $pesan = session($tipe);

        if (blank($pesan)) {
            continue;
        }

        foreach ((array) $pesan as $isi) {
            $toasts[] = ['tipe' => $tipe, 'pesan' => $isi];
        }
    }
@endphp

@if (count($toasts))
    <div class="pointer-events-none fixed inset-x-0 top-0 z-50 flex flex-col items-end gap-3 p-4 sm:p-6">
        @foreach ($toasts as $toast)
            <div
                x-data="{ tampil: true }"
                x-show="tampil"
                x-init="setTimeout(() => tampil = false, {{ $durasi }})"
                x-transition:enter="transform ease-out duration-300"
                x-transition:enter-start="translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transform ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-full opacity-0"
                role="status"
                aria-live="polite"
                class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg border bg-white shadow-lg {{ $gaya[$toast['tipe']]['kartu'] }}"
            >
                <div class="flex items-start gap-3 p-4">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $gaya[$toast['tipe']]['ikon'] }}">
                        @switch($toast['tipe'])
                            @case('success')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                @break
                            @case('error')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                @break
                            @case('warning')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                @break
                            @default
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-4a1 1 0 100 2 1 1 0 000-2zM9 9a1 1 0 012 0v5a1 1 0 11-2 0V9z" clip-rule="evenodd" />
                                </svg>
                        @endswitch
                    </span>

                    <p class="flex-1 pt-1 text-sm font-medium leading-relaxed text-gray-800">{{ $toast['pesan'] }}</p>

                    <button
                        type="button"
                        x-on:click="tampil = false"
                        class="-mr-1 -mt-1 shrink-0 rounded-md p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300"
                        aria-label="{{ __('Tutup notifikasi') }}"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div
                    class="toast-progress h-1 w-full origin-left {{ $gaya[$toast['tipe']]['bar'] }}"
                    style="animation-duration: {{ $durasi }}ms"
                ></div>
            </div>
        @endforeach
    </div>
@endif
