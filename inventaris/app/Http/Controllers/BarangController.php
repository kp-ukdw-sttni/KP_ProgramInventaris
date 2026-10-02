<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\PreservesListState;
use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Ruangan;
use App\Support\DocxTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BarangController extends Controller implements HasMiddleware
{
    use PreservesListState;

    private const PER_PAGE = 15;

    /**
     * Batasi aksi tulis sesuai permission yang dimiliki user.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:create barang', only: ['create', 'store']),
            new Middleware('permission:edit barang', only: ['edit', 'update']),
            new Middleware('permission:delete barang', only: ['destroy']),
            new Middleware('permission:import barang', only: ['import', 'importForm', 'importTemplate']),
            new Middleware('permission:export barang', only: ['export']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Paginate by 15 items per page and append query parameters to the links
        $barangs = $this->filteredQuery($request)->paginate(self::PER_PAGE)->withQueryString();

        // AJAX request returns only the partial table HTML
        if ($request->ajax()) {
            return view('barang.partials.table', compact('barangs'))->render();
        }

        // Normal request gets helper data for dropdown filters
        $ruangans = Ruangan::orderBy('urutan')->orderBy('nama_ruangan')->get();
        $kondisis = ['Baik', 'Kurang Baik', 'Rusak', 'Mati'];
        $kategoris = KategoriBarang::all();

        return view('barang.index', compact('barangs', 'ruangans', 'kondisis', 'kategoris'));
    }

    /**
     * Bangun query daftar barang sesuai filter yang sedang aktif.
     */
    private function filteredQuery(Request $request): Builder
    {
        $query = Barang::with(['ruangan', 'kategoriBarang']);

        // Search by nama_fasilitas or kode_inventaris
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_fasilitas', 'like', '%'.$search.'%')
                    ->orWhere('kode_inventaris', 'like', '%'.$search.'%');
            });
        }

        // Filter by ruangan_id
        if ($request->filled('ruangan_id')) {
            $query->where('ruangan_id', $request->input('ruangan_id'));
        }

        // Filter by kategori_id
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->input('kategori_id'));
        }

        // Filter by kondisi
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->input('kondisi'));
        }

        return $query;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $ruangans = Ruangan::orderBy('urutan')->orderBy('nama_ruangan')->get();
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
        $detailRuang = [];

        foreach ($validated['ruangan_ids'] as $ruanganId) {
            $ruangan = Ruangan::findOrFail($ruanganId);
            $prefix = Barang::kodePrefix($ruangan);
            $seq = Barang::nextSequence($ruangan);
            $kodeAwal = Barang::formatKode($prefix, $seq);

            for ($i = 1; $i <= $validated['jumlah']; $i++) {
                Barang::create([
                    'ruangan_id' => $ruanganId,
                    'kategori_id' => $validated['kategori_id'] ?? null,
                    'nama_fasilitas' => $validated['jumlah'] > 1
                        ? $validated['nama_fasilitas'].' '.$i
                        : $validated['nama_fasilitas'],
                    'kode_inventaris' => Barang::formatKode($prefix, $seq++),
                    'tahun_pembelian' => $validated['tahun_pembelian'] ?? null,
                    'kondisi' => $validated['kondisi'],
                    'keterangan' => $validated['keterangan'] ?? null,
                ]);

                $totalUnits++;
            }

            $detailRuang[] = [
                'nama' => $ruangan->nama_ruangan,
                'jumlah' => $validated['jumlah'],
                'kode_awal' => $kodeAwal,
                'kode_akhir' => Barang::formatKode($prefix, $seq - 1),
            ];
        }

        $namaBarang = $validated['jumlah'] > 1
            ? $this->baseNama($validated['nama_fasilitas'])
            : $validated['nama_fasilitas'];

        $pesan = $totalUnits.' unit '.$namaBarang.' berhasil ditambahkan';

        if (count($detailRuang) === 1) {
            $ruang = $detailRuang[0];
            $pesan .= ' di '.$ruang['nama'].' (kode '.$ruang['kode_awal'];

            if ($ruang['jumlah'] > 1) {
                $pesan .= ' s.d. '.$ruang['kode_akhir'];
            }

            $pesan .= ').';
        } else {
            $daftar = collect($detailRuang)
                ->map(fn ($ruang) => $ruang['nama'].' ('.$ruang['jumlah'].' unit)')
                ->implode(', ');

            $pesan .= ' di '.count($detailRuang).' ruangan: '.$daftar.'.';
        }

        // Filter tetap dipertahankan, halaman dikembalikan ke awal supaya
        // barang baru yang cocok dengan filter terlihat langsung.
        $state = $this->listState($request, 'barang', keepPage: false);

        return redirect()->route('barang.index', $state)->with('success', $pesan);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang)
    {
        $ruangans = Ruangan::orderBy('urutan')->orderBy('nama_ruangan')->get();
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

        $ruanganIdSebelumnya = $barang->ruangan_id;
        $kode = $barang->kode_inventaris;

        $barang->update($validated);

        $pesan = 'Barang "'.$barang->nama_fasilitas.'" ('.$kode.') berhasil diperbarui.';

        if ((int) $ruanganIdSebelumnya !== (int) $barang->ruangan_id) {
            $ruangAsal = Ruangan::find($ruanganIdSebelumnya);
            $ruangBaru = $barang->ruangan;

            if ($ruangAsal && $ruangBaru) {
                $pesan .= ' Barang ini dipindahkan dari '.$ruangAsal->nama_ruangan
                    .' ke '.$ruangBaru->nama_ruangan.'.';
            }
        }

        $state = $this->listState($request, 'barang');

        return redirect()->route('barang.index', $state)->with('success', $pesan);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Barang $barang)
    {
        $barang->loadMissing('ruangan');

        $nama = $barang->nama_fasilitas;
        $kode = $barang->kode_inventaris;
        $namaRuangan = $barang->ruangan?->nama_ruangan;

        $barang->delete();

        $pesan = '1 unit barang "'.$nama.'" ('.$kode.') berhasil dihapus';

        if ($namaRuangan) {
            $pesan .= ' dari '.$namaRuangan;
        }

        // Hitung ulang sisa hasil filter supaya halaman tidak melompat ke
        // halaman kosong setelah baris terakhir dihapus.
        $sisa = $this->filteredQuery($request)->toBase()->getCountForPagination();
        $state = $this->listState($request, 'barang', $sisa, self::PER_PAGE);

        return redirect()->route('barang.index', $state)->with('success', $pesan.'.');
    }

    /**
     * Strip the trailing unit sequence from a facility name,
     * e.g. "Kursi Kuliahan Chitose 1" -> "Kursi Kuliahan Chitose".
     */
    private function baseNama(string $nama): string
    {
        return preg_replace('/\s+\d+\z/', '', $nama) ?: $nama;
    }

    /**
     * Export data barang ke format JSON dengan relasi ruangan dan kategori.
     */
    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');

        $sekarang = now()->locale('id');
        $baseName = 'data_inventaris_'.$sekarang->translatedFormat('F').$sekarang->format('Y');

        if ($format === 'json') {
            $query = $this->filteredQuery($request)->with(['ruangan', 'kategoriBarang']);

            $exportData = $query->orderBy('kode_inventaris')->get()->map(function ($barang) {
                return [
                    'id' => $barang->id,
                    'ruangan_id' => $barang->ruangan_id,
                    'kategori_id' => $barang->kategori_id,
                    'nama_fasilitas' => $barang->nama_fasilitas,
                    'kode_inventaris' => $barang->kode_inventaris,
                    'tahun_pembelian' => $barang->tahun_pembelian,
                    'kondisi' => $barang->kondisi,
                    'keterangan' => $barang->keterangan,
                    'created_at' => $barang->created_at,
                    'updated_at' => $barang->updated_at,
                    'ruangan' => $barang->ruangan ? [
                        'id' => $barang->ruangan->id,
                        'nama_ruangan' => $barang->ruangan->nama_ruangan,
                        'deskripsi' => $barang->ruangan->deskripsi,
                        'urutan' => $barang->ruangan->urutan,
                        'created_at' => $barang->ruangan->created_at,
                        'updated_at' => $barang->ruangan->updated_at,
                    ] : null,
                    'kategori_barang' => $barang->kategoriBarang ? [
                        'id' => $barang->kategoriBarang->id,
                        'nama_kategori' => $barang->kategoriBarang->nama_kategori,
                        'created_at' => $barang->kategoriBarang->created_at,
                        'updated_at' => $barang->kategoriBarang->updated_at,
                    ] : null,
                ];
            });

            $filename = $baseName.'.json';

            $response = new StreamedResponse(function () use ($exportData) {
                echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            });

            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
            $response->headers->set('Cache-Control', 'no-cache, must-revalidate');

            return $response;
        }

        $query = $this->filteredQuery($request)->with(['ruangan', 'kategoriBarang']);

        if ($format === 'word' || $format === 'docx') {
            $barangs = $query->orderBy('kode_inventaris')->get();

            $rows = $barangs->map(fn ($barang) => [
                $barang->kode_inventaris,
                $barang->nama_fasilitas,
                $barang->ruangan?->nama_ruangan,
                $barang->kategoriBarang?->nama_kategori,
                $barang->kondisi,
                $barang->tahun_pembelian,
                $barang->keterangan,
            ])->all();

            $binary = DocxTable::make(
                'Daftar Inventaris Sarana & Prasarana STTNI',
                ['Kode Inventaris', 'Nama Fasilitas', 'Ruangan', 'Kategori', 'Kondisi', 'Tahun', 'Keterangan'],
                $rows,
                landscape: true,
                options: [
                    'logo' => public_path('images/logo-sttni.png'),
                    'brand' => [
                        ['text' => 'INVENTARIS', 'color' => '2563EB'],
                        ['text' => ' STTNI', 'color' => '111827'],
                    ],
                ],
            );

            return response($binary, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="'.$baseName.'.docx"',
                'Cache-Control' => 'no-cache, must-revalidate',
            ]);
        }

        $barangs = $query->orderBy('kode_inventaris')->get();

        $filename = $baseName.'.csv';

        $response = new StreamedResponse(function () use ($barangs) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Dibuat titik koma agar langsung terpisah rapi di Excel
            // dengan pengaturan regional Indonesia.
            fputcsv($handle, [
                'kode_inventaris',
                'nama_fasilitas',
                'ruangan',
                'kategori',
                'kondisi',
                'tahun_pembelian',
                'keterangan',
            ], ';');

            foreach ($barangs as $barang) {
                fputcsv($handle, [
                    $barang->kode_inventaris,
                    $barang->nama_fasilitas,
                    $barang->ruangan?->nama_ruangan,
                    $barang->kategoriBarang?->nama_kategori,
                    $barang->kondisi,
                    $barang->tahun_pembelian,
                    $barang->keterangan,
                ], ';');
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        $response->headers->set('Cache-Control', 'no-cache, must-revalidate');

        return $response;
    }

    /**
     * Tampilkan form import barang.
     */
    public function importForm()
    {
        return view('barang.import');
    }

    /**
     * Unduh template CSV kosong (hanya header) untuk diisi klien.
     */
    public function importTemplate(): StreamedResponse
    {
        $filename = 'template_import_barang.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Titik koma agar kolom langsung terpisah di Excel regional Indonesia.
            fputcsv($handle, [
                'kode_inventaris',
                'nama_fasilitas',
                'ruangan',
                'kategori',
                'kondisi',
                'tahun_pembelian',
                'keterangan',
            ], ';');

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Import data barang dari file CSV.
     *
     * Kolom CSV: kode_inventaris (opsional), nama_fasilitas, ruangan,
     * kategori, kondisi, tahun_pembelian, keterangan. Bila kode_inventaris
     * dikosongkan, sistem membuat kode otomatis sesuai format ruangan.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:51200'], // max 50MB
            'import_mode' => ['nullable', 'in:skip,replace'],
        ]);

        $importMode = $request->input('import_mode', 'skip');
        $file = $request->file('file');

        // Mode replace menimpa data ber-kode sama, jadi dibatasi untuk
        // pemegang permission 'replace barang' (Admin Sarpras).
        if ($importMode === 'replace' && ! $request->user()->can('replace barang')) {
            return redirect()->route('barang.import.form')
                ->with('error', 'Mode "Ganti (Replace)" hanya dapat digunakan oleh Admin Sarpras. Silakan gunakan mode "Lewati (Skip)".');
        }

        [$rows, $header] = $this->parseCsv($file->getRealPath());

        if ($rows === null) {
            return redirect()->route('barang.import.form')
                ->with('error', 'File CSV tidak dapat dibaca.');
        }

        $wajib = ['nama_fasilitas', 'ruangan', 'kondisi'];
        $header = array_map(fn ($h) => trim(strtolower($h)), $header);

        $kurang = array_diff($wajib, $header);
        if (! empty($kurang)) {
            return redirect()->route('barang.import.form')
                ->with('error', 'Kolom wajib tidak ditemukan: '.implode(', ', $kurang)
                    .'. Gunakan file hasil Export atau template CSV.');
        }

        // Siapkan lookup nama -> id (case-insensitive) untuk ruangan & kategori.
        // Nama dinormalisasi (huruf kecil, spasi dirapatkan) agar beda kapital
        // atau spasi ganda tidak dianggap sebagai ruangan/kategori berbeda.
        $daftarRuangan = Ruangan::get();
        $daftarKategori = KategoriBarang::get();
        $ruanganMap = $daftarRuangan->keyBy(fn ($r) => $this->normalizeNama($r->nama_ruangan));
        $kategoriMap = $daftarKategori->keyBy(fn ($k) => $this->normalizeNama($k->nama_kategori));

        $errors = [];
        $validItems = [];
        $imported = 0;
        $skipped = 0;
        $replaced = 0;
        $nomor = 1; // baris 1 = header

        foreach ($rows as $row) {
            $nomor++;

            if ($this->rowKosong($row)) {
                continue;
            }

            $data = [];
            foreach ($header as $i => $kolom) {
                $data[$kolom] = isset($row[$i]) ? trim((string) $row[$i]) : '';
            }

            $namaRuangan = $data['ruangan'] ?? '';
            $namaKategori = $data['kategori'] ?? '';

            $ruangan = $namaRuangan !== ''
                ? $ruanganMap->get($this->normalizeNama($namaRuangan))
                : null;

            if (! $ruangan) {
                $saran = $this->saranNama($namaRuangan, $daftarRuangan->pluck('nama_ruangan'));
                $errors[] = "Baris {$nomor}: ruangan \"{$namaRuangan}\" tidak ditemukan."
                    .($saran ? " Mungkin maksud Anda \"{$saran}\"?" : ' Periksa daftar ruangan di menu Ruangan.');

                continue;
            }

            $kategori = null;
            if ($namaKategori !== '') {
                $kategori = $kategoriMap->get($this->normalizeNama($namaKategori));
                if (! $kategori) {
                    $saran = $this->saranNama($namaKategori, $daftarKategori->pluck('nama_kategori'));
                    $errors[] = "Baris {$nomor}: kategori \"{$namaKategori}\" tidak ditemukan."
                        .($saran ? " Mungkin maksud Anda \"{$saran}\"?" : ' Periksa daftar kategori di menu Kategori.');

                    continue;
                }
            }

            $tahun = $data['tahun_pembelian'] ?? '';
            $tahun = $tahun === '' ? null : (int) $tahun;

            $item = [
                'ruangan_id' => $ruangan->id,
                'kategori_id' => $kategori?->id,
                'nama_fasilitas' => $data['nama_fasilitas'] ?? '',
                'kode_inventaris' => ($data['kode_inventaris'] ?? '') !== '' ? $data['kode_inventaris'] : null,
                'tahun_pembelian' => $tahun,
                'kondisi' => $data['kondisi'] ?? '',
                'keterangan' => ($data['keterangan'] ?? '') !== '' ? $data['keterangan'] : null,
            ];

            $validator = Validator::make($item, [
                'ruangan_id' => ['required', 'integer', 'exists:ruangan,id'],
                'kategori_id' => ['nullable', 'integer', 'exists:kategori_barang,id'],
                'nama_fasilitas' => ['required', 'string', 'max:255'],
                'kode_inventaris' => ['nullable', 'string', 'max:255'],
                'tahun_pembelian' => ['nullable', 'integer', 'between:1900,2100'],
                'kondisi' => ['required', Rule::in(['Baik', 'Kurang Baik', 'Rusak', 'Mati'])],
                'keterangan' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                $errors[] = "Baris {$nomor} (kode: ".($item['kode_inventaris'] ?: 'N/A').'): '
                    .implode(', ', $validator->errors()->all());

                continue;
            }

            $validItems[] = $validator->validated();
        }

        if (! empty($errors)) {
            $tampil = array_slice($errors, 0, 20);
            $pesan = implode("\n", $tampil);
            if (count($errors) > 20) {
                $pesan .= "\n... dan ".(count($errors) - 20).' error lainnya.';
            }

            return redirect()->route('barang.import.form')->with('error', "Terdapat error validasi:\n".$pesan);
        }

        if (empty($validItems)) {
            return redirect()->route('barang.import.form')
                ->with('error', 'Tidak ada data valid yang bisa diimpor.');
        }

        try {
            DB::transaction(function () use ($validItems, $importMode, &$imported, &$skipped, &$replaced) {
                // Backup data lama sebelum import sebagai jaring pengaman.
                if (Barang::count() > 0) {
                    $backupFile = storage_path('app/backup_barang_import_'.now()->format('Ymd_His').'.json');
                    file_put_contents($backupFile, Barang::with(['ruangan', 'kategoriBarang'])->get()->toJson(JSON_PRETTY_PRINT));
                }

                // Hindari bentrok kode_inventaris ganda di dalam file yang sama.
                $kodeDiFile = [];

                foreach ($validItems as $item) {
                    $kode = $item['kode_inventaris'] ?? null;

                    // Kode kosong: biarkan sistem yang membuat kode otomatis
                    // mengikuti format & urutan ruangan yang bersangkutan.
                    if (blank($kode)) {
                        $ruangan = Ruangan::find($item['ruangan_id']);

                        Barang::create([
                            'ruangan_id' => $item['ruangan_id'],
                            'kategori_id' => $item['kategori_id'] ?? null,
                            'nama_fasilitas' => $item['nama_fasilitas'],
                            'kode_inventaris' => Barang::generateKodeInventaris($ruangan),
                            'tahun_pembelian' => $item['tahun_pembelian'] ?? null,
                            'kondisi' => $item['kondisi'],
                            'keterangan' => $item['keterangan'] ?? null,
                        ]);
                        $imported++;

                        continue;
                    }

                    if (isset($kodeDiFile[$kode])) {
                        $skipped++;

                        continue;
                    }
                    $kodeDiFile[$kode] = true;

                    $existing = Barang::where('kode_inventaris', $kode)->first();

                    if ($existing && $importMode === 'skip') {
                        $skipped++;

                        continue;
                    }

                    if ($existing && $importMode === 'replace') {
                        $existing->update([
                            'ruangan_id' => $item['ruangan_id'],
                            'kategori_id' => $item['kategori_id'] ?? null,
                            'nama_fasilitas' => $item['nama_fasilitas'],
                            'tahun_pembelian' => $item['tahun_pembelian'] ?? null,
                            'kondisi' => $item['kondisi'],
                            'keterangan' => $item['keterangan'] ?? null,
                        ]);
                        $replaced++;

                        continue;
                    }

                    Barang::create([
                        'ruangan_id' => $item['ruangan_id'],
                        'kategori_id' => $item['kategori_id'] ?? null,
                        'nama_fasilitas' => $item['nama_fasilitas'],
                        'kode_inventaris' => $kode,
                        'tahun_pembelian' => $item['tahun_pembelian'] ?? null,
                        'kondisi' => $item['kondisi'],
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);
                    $imported++;
                }
            });
        } catch (\Exception $e) {
            return redirect()->route('barang.import.form')->with('error', 'Gagal mengimpor data: '.$e->getMessage());
        }

        $message = "Import berhasil! Ditambahkan: {$imported}";
        if ($skipped > 0) {
            $message .= ", Dilewati: {$skipped}";
        }
        if ($replaced > 0) {
            $message .= ", Diganti: {$replaced}";
        }
        $message .= ' (Total baris valid: '.count($validItems).')';

        return redirect()->route('barang.index')->with('success', $message);
    }

    /**
     * Baca CSV menjadi array baris. Mendukung pemisah koma, titik koma,
     * atau tab, serta menghapus BOM.
     *
     * @return array{0: array<int, array<int, string>>|null, 1: array<int, string>}
     */
    private function parseCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [null, []];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [[], []];
        }

        // Deteksi delimiter dari baris header.
        $delimiters = [',' => substr_count($firstLine, ','), ';' => substr_count($firstLine, ';'), "\t" => substr_count($firstLine, "\t")];
        arsort($delimiters);
        $delimiter = array_key_first($delimiters) ?: ',';

        rewind($handle);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
        }
        fclose($handle);

        if (empty($rows)) {
            return [[], []];
        }

        $header = array_shift($rows);

        // Buang BOM di kolom pertama.
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        return [$rows, $header];
    }

    /**
     * Cek apakah seluruh kolom pada baris kosong.
     */
    private function rowKosong(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Normalisasi nama untuk pencocokan: huruf kecil, spasi dirapatkan.
     */
    private function normalizeNama(string $nama): string
    {
        return strtolower(trim((string) preg_replace('/\s+/', ' ', $nama)));
    }

    /**
     * Cari nama terdekat (mirip) dari daftar nama untuk saran typo.
     *
     * @param  Collection<int, string>  $namaList
     */
    private function saranNama(string $input, $namaList, int $threshold = 70): ?string
    {
        $inputNorm = $this->normalizeNama($input);
        $terbaik = null;
        $skorTerbaik = 0.0;

        foreach ($namaList as $nama) {
            similar_text($inputNorm, $this->normalizeNama((string) $nama), $skor);

            if ($skor > $skorTerbaik) {
                $skorTerbaik = $skor;
                $terbaik = $nama;
            }
        }

        return $skorTerbaik >= $threshold ? $terbaik : null;
    }
}
