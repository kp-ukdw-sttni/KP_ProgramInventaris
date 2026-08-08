<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Ruangan;

class DashboardController extends Controller
{
    /**
     * Show the dashboard summary with percentages.
     */
    public function __invoke()
    {
        $totalBarang = Barang::count();
        $totalRuangan = Ruangan::count();

        $kondisis = ['Baik', 'Kurang Baik', 'Rusak', 'Mati'];
        $kondisiCounts = Barang::selectRaw('kondisi, count(*) as total')
            ->groupBy('kondisi')
            ->pluck('total', 'kondisi')
            ->toArray();

        $kondisiPercentages = [];
        foreach ($kondisis as $kondisi) {
            $count = $kondisiCounts[$kondisi] ?? 0;
            $kondisiPercentages[$kondisi] = $totalBarang > 0 ? round(($count / $totalBarang) * 100, 1) : 0;
        }

        $kondisiBaik = $kondisiCounts['Baik'] ?? 0;
        $kondisiRusak = ($kondisiCounts['Rusak'] ?? 0) + ($kondisiCounts['Mati'] ?? 0);
        $persentaseBaik = $totalBarang > 0 ? round(($kondisiBaik / $totalBarang) * 100, 1) : 0;
        $persentaseRusak = $totalBarang > 0 ? round(($kondisiRusak / $totalBarang) * 100, 1) : 0;
        $persentaseLayak = $totalBarang > 0
            ? round((($kondisiBaik + ($kondisiCounts['Kurang Baik'] ?? 0)) / $totalBarang) * 100, 1)
            : 0;

        $kategoris = KategoriBarang::withCount('barangs')
            ->orderByDesc('barangs_count')
            ->get()
            ->map(fn ($kategori) => [
                'nama' => $kategori->nama_kategori,
                'total' => $kategori->barangs_count,
                'persentase' => $totalBarang > 0 ? round(($kategori->barangs_count / $totalBarang) * 100, 1) : 0,
            ]);

        return view('dashboard', compact(
            'totalBarang',
            'totalRuangan',
            'kondisis',
            'kondisiCounts',
            'kondisiPercentages',
            'kondisiBaik',
            'kondisiRusak',
            'persentaseBaik',
            'persentaseRusak',
            'persentaseLayak',
            'kategoris'
        ));
    }
}
