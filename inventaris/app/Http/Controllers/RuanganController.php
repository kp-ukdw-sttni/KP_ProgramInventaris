<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use App\Http\Controllers\Concerns\PreservesListState;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    use PreservesListState;

    private const PER_PAGE = 15;

    /**
     * Display a listing of rooms.
     */
    public function index(Request $request)
    {
        $ruangans = Ruangan::withCount('barangs')->paginate(self::PER_PAGE)->withQueryString();

        return view('ruangan.index', compact('ruangans'));
    }

    /**
     * Display the asset breakdown of a room grouped by category and item type.
     */
    public function show(Ruangan $ruangan)
    {
        $perKategori = $ruangan->barangs()
            ->selectRaw('kategori_id, COUNT(*) as total')
            ->groupBy('kategori_id')
            ->with('kategoriBarang')
            ->get();

        $totalBarang = $perKategori->sum('total');

        $perNamaBarang = $ruangan->barangs()
            ->pluck('nama_fasilitas')
            ->map(fn ($nama) => static::baseNamaFasilitas($nama))
            ->countBy()
            ->sortDesc();

        return view('ruangan.show', compact('ruangan', 'perKategori', 'perNamaBarang', 'totalBarang'));
    }

    /**
     * Normalize a facility name by stripping the trailing unit sequence,
     * e.g. "Kursi Kuliahan Chitose 1" -> "Kursi Kuliahan Chitose".
     */
    private static function baseNamaFasilitas(string $nama): string
    {
        return preg_replace('/\s+\d+\z/', '', $nama) ?: $nama;
    }

    /**
     * Show the form for creating a new room.
     */
    public function create()
    {
        return view('ruangan.create');
    }

    /**
     * Store a newly created room.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_ruangan' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $ruangan = Ruangan::create($validated);

        $state = $this->listState($request, 'ruangan');

        return redirect()->route('ruangan.index', $state)
            ->with('success', 'Ruangan "' . $ruangan->nama_ruangan . '" berhasil ditambahkan. Ruangan ini masih kosong, silakan tambahkan barang ke dalamnya.');
    }

    /**
     * Show the form for editing a room.
     */
    public function edit(Ruangan $ruangan)
    {
        return view('ruangan.edit', compact('ruangan'));
    }

    /**
     * Update a room.
     */
    public function update(Request $request, Ruangan $ruangan)
    {
        $validated = $request->validate([
            'nama_ruangan' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $jumlahBarang = $ruangan->barangs()->count();
        $namaSebelumnya = $ruangan->nama_ruangan;

        $ruangan->update($validated);

        $pesan = 'Ruangan "' . $ruangan->nama_ruangan . '" berhasil diperbarui.';

        if ($namaSebelumnya !== $ruangan->nama_ruangan) {
            $pesan .= ' Nama lama: "' . $namaSebelumnya . '".';
        }

        if ($jumlahBarang > 0) {
            $pesan .= ' ' . $jumlahBarang . ' unit barang di dalamnya tetap berada di ruangan ini.';
        }

        $state = $this->listState($request, 'ruangan');

        return redirect()->route('ruangan.index', $state)->with('success', $pesan);
    }

    /**
     * Remove a room.
     */
    public function destroy(Request $request, Ruangan $ruangan)
    {
        $nama = $ruangan->nama_ruangan;
        $jumlahBarang = $ruangan->barangs()->count();

        $ruangan->delete();

        $pesan = 'Ruangan "' . $nama . '" berhasil dihapus';

        if ($jumlahBarang > 0) {
            $pesan .= ' beserta ' . $jumlahBarang . ' unit barang di dalamnya';
        }

        $sisa = Ruangan::withCount('barangs')->toBase()->getCountForPagination();
        $state = $this->listState($request, 'ruangan', $sisa, self::PER_PAGE);

        return redirect()->route('ruangan.index', $state)->with('success', $pesan . '.');
    }
}
