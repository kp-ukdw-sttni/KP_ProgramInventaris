<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Ruangan;
use App\Models\KategoriBarang;
use Illuminate\Validation\Rule;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Barang::with(['ruangan', 'kategoriBarang']);

        // Search by nama_fasilitas or kode_inventaris
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_fasilitas', 'like', '%' . $search . '%')
                  ->orWhere('kode_inventaris', 'like', '%' . $search . '%');
            });
        }

        // Filter by ruangan_id
        if ($request->filled('ruangan_id')) {
            $query->where('ruangan_id', $request->input('ruangan_id'));
        }

        // Filter by kondisi
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->input('kondisi'));
        }

        // Paginate by 15 items per page and append query parameters to the links
        $barangs = $query->paginate(15)->withQueryString();

        // AJAX request returns only the partial table HTML
        if ($request->ajax()) {
            return view('barang.partials.table', compact('barangs'))->render();
        }

        // Normal request gets helper data for dropdown filters
        $ruangans = Ruangan::all();
        $kondisis = ['Baik', 'Kurang Baik', 'Rusak', 'Mati'];
        $kategoris = KategoriBarang::all();

        return view('barang.index', compact('barangs', 'ruangans', 'kondisis', 'kategoris'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ruangans = Ruangan::all();
        $kategoris = KategoriBarang::all();
        $kondisis = ['Baik', 'Kurang Baik', 'Rusak', 'Mati'];

        return view('barang.create', compact('ruangans', 'kategoris', 'kondisis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ruangan_ids' => ['required', 'array', 'min:1'],
            'ruangan_ids.*' => ['exists:ruangan,id'],
            'kategori_id' => ['nullable', 'exists:kategori_barang,id'],
            'nama_fasilitas' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:500'],
            'kondisi' => ['required', Rule::in(['Baik', 'Kurang Baik', 'Rusak', 'Mati'])],
            'tahun_pembelian' => ['nullable', 'integer', 'between:1900,2100'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $totalUnits = 0;

        foreach ($validated['ruangan_ids'] as $ruanganId) {
            $ruangan = Ruangan::findOrFail($ruanganId);
            $prefix = Barang::kodePrefix($ruangan);
            $seq = Barang::nextSequence($ruangan);

            for ($i = 1; $i <= $validated['jumlah']; $i++) {
                Barang::create([
                    'ruangan_id' => $ruanganId,
                    'kategori_id' => $validated['kategori_id'] ?? null,
                    'nama_fasilitas' => $validated['jumlah'] > 1
                        ? $validated['nama_fasilitas'] . ' ' . $i
                        : $validated['nama_fasilitas'],
                    'kode_inventaris' => Barang::formatKode($prefix, $seq++),
                    'tahun_pembelian' => $validated['tahun_pembelian'] ?? null,
                    'kondisi' => $validated['kondisi'],
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);

                $totalUnits++;
            }
        }

        return redirect()->route('barang.index')
            ->with('success', $totalUnits . ' unit barang berhasil ditambahkan di ' . count($validated['ruangan_ids']) . ' ruangan, masing-masing dengan kode inventaris unik.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang)
    {
        $ruangans = Ruangan::all();
        $kategoris = KategoriBarang::all();
        $kondisis = ['Baik', 'Kurang Baik', 'Rusak', 'Mati'];

        return view('barang.edit', compact('barang', 'ruangans', 'kategoris', 'kondisis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
        $validated = $request->validate([
            'ruangan_id' => ['required', 'exists:ruangan,id'],
            'kategori_id' => ['nullable', 'exists:kategori_barang,id'],
            'nama_fasilitas' => ['required', 'string', 'max:255'],
            'kondisi' => ['required', Rule::in(['Baik', 'Kurang Baik', 'Rusak', 'Mati'])],
            'tahun_pembelian' => ['nullable', 'integer', 'between:1900,2100'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $barang->update($validated);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $barang)
    {
        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus!');
    }
}
