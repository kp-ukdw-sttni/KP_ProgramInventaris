<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;

class RuanganController extends Controller
{
    /**
     * Display a listing of rooms.
     */
    public function index()
    {
        $ruangans = Ruangan::withCount('barangs')->paginate(15);
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

        Ruangan::create($validated);

        return redirect()->route('ruangan.index')->with('success', 'Ruangan berhasil ditambahkan!');
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

        $ruangan->update($validated);

        return redirect()->route('ruangan.index')->with('success', 'Ruangan berhasil diperbarui!');
    }

    /**
     * Remove a room.
     */
    public function destroy(Ruangan $ruangan)
    {
        $ruangan->delete();

        return redirect()->route('ruangan.index')->with('success', 'Ruangan berhasil dihapus!');
    }
}
