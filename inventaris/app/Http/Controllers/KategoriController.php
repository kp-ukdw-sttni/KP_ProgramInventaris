<?php

namespace App\Http\Controllers;

use App\Models\KategoriBarang;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        $columns = ['nama_kategori', 'id'];
        $column = $request->query('sort', 'nama_kategori');
        if (! in_array($column, $columns, true)) {
            $column = 'nama_kategori';
        }

        $direction = strtolower($request->query('direction', 'asc'));
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $kategoris = KategoriBarang::withCount('barangs')
            ->when($column === 'id', fn ($q) => $q->orderBy('id', $direction))
            ->when($column === 'nama_kategori', fn ($q) => $q->orderBy('nama_kategori', $direction))
            ->paginate(15)
            ->withQueryString();

        return view('kategori.index', compact('kategoris', 'column', 'direction'));
    }

    /**
     * Display the asset breakdown of a category grouped by item type.
     */
    public function show(KategoriBarang $kategori)
    {
        $totalBarang = $kategori->barangs()->count();

        $perNamaBarang = $kategori->barangs()
            ->pluck('nama_fasilitas')
            ->map(fn ($nama) => static::baseNamaFasilitas($nama))
            ->countBy()
            ->sortDesc();

        return view('kategori.show', compact('kategori', 'perNamaBarang', 'totalBarang'));
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
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('kategori.create');
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', Rule::unique('kategori_barang', 'nama_kategori')],
        ]);

        KategoriBarang::create($validated);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    /**
     * Show the form for editing a category.
     */
    public function edit(KategoriBarang $kategori)
    {
        return view('kategori.edit', compact('kategori'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, KategoriBarang $kategori)
    {
        $validated = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:255', Rule::unique('kategori_barang', 'nama_kategori')->ignore($kategori->id)],
        ]);

        $kategori->update($validated);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(KategoriBarang $kategori)
    {
        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus!');
    }
}