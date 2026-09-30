<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait PreservesListState
{
    /**
     * Kunci query string yang membentuk "tampilan daftar" sebuah halaman index.
     */
    protected const LIST_KEYS = [
        'barang' => ['search', 'ruangan_id', 'kategori_id', 'kondisi', 'page'],
        'ruangan' => ['page'],
        'kategori' => ['sort', 'direction', 'page'],
    ];

    /**
     * Kumpulkan query string tampilan daftar yang sedang aktif supaya pencarian,
     * filter, sorting, dan posisi halaman tidak hilang setelah tambah, edit,
     * atau hapus.
     *
     * @param  string  $resource  Kunci pada self::LIST_KEYS
     * @param  int|null  $total  Jumlah baris hasil filter, dipakai untuk menjepit
     *                           halaman supaya tidak mendarat di halaman kosong.
     * @param  bool  $keepPage  False untuk kembali ke halaman pertama, mis. setelah tambah.
     */
    protected function listState(Request $request, string $resource, ?int $total = null, int $perPage = 15, bool $keepPage = true): array
    {
        $state = [];
        $diminta = $keepPage ? max(1, (int) $request->query('page', 1)) : null;

        foreach (self::LIST_KEYS[$resource] ?? [] as $key) {
            $value = $request->query($key);

            if ($value === null || $value === '') {
                continue;
            }

            if ($key === 'page') {
                // Setelah tambah, data baru harus langsung terlihat.
                if (! $keepPage) {
                    continue;
                }

                $dijepit = $this->clampPage((int) $value, $total, $perPage);

                // Halaman 1 adalah default, jadi cukup dibuang dari URL kalau
                // nomor halaman yang diminta sudah tidak ada lagi.
                if ($dijepit !== $diminta) {
                    continue;
                }
            }

            $state[$key] = $value;
        }

        return $state;
    }

    /**
     * Jepit nomor halaman ke rentang yang benar-benar punya data.
     */
    private function clampPage(int $page, ?int $total, int $perPage): int
    {
        $page = max(1, $page);

        if ($total === null) {
            return $page;
        }

        return min($page, max(1, (int) ceil($total / $perPage)));
    }
}
