<?php

namespace App\Console\Commands;

use App\Models\Barang;
use App\Models\KategoriBarang;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InventoryCategorize extends Command
{
    protected $signature = 'inventory:categorize';

    protected $description = 'Kategorikan barang secara otomatis berdasarkan kata kunci nama fasilitas.';

    /**
     * Barang yang bersifat struktural/sarana (bukan perlengkapan) -> dibiarkan tanpa kategori.
     */
    private const SKIP_PATTERN = '/^kamar\b|^ruang doa\b|^ruang bapak\b|^gudang\b|^lapangan\b|^ring basket\b|^kolam\b|^penampung air\b|^torn\b|^kompor\b|^tabung gas\b|^mobil\b|^motor\b|^ops\b|\btenis meja\b/i';

    /**
     * Urutan prioritas kategori + pola kata kunci (regex, case-insensitive).
     */
    private const RULES = [
        'Elektronik' => '/\bac\b|cctv|computer|komputer|\bpc\b|\bcpu\b|laptop|dispenser|\bjam\b|kipas|kulkas|lampu|mesin photo|mesin ketik|masin ketik|photo copy|pengecek suhu|printer|telepon|wifi|wi-fi|skavolt|mesin potong/i',
        'Alat Kebersihan' => '/ember|baskom|keran|selang|wastafel|hand sanitizer|sabun|sprot|penyiram|gerobak|sampah|drum|kontener/i',
        'Mebel & Interior' => '/kursi|meja|lemari|cabinet file|sofa|mebel|rak buku|rak\b|mimbar|tempat tidur|tempat kartu|tempat kertas|tempat koran|figura|figuran|cermin|madding|mading|bendera|struktur organisasi|papan nama|tikar|sertifikat/i',
        'Peralatan Kantor (ATK)' => '/box file|folder|laci|kerajang buku|keranjang buku|katalog manual/i',
        'Media Pembelajaran & Sound System' => '/lcd|projector|screen|speaker|sound|londspeaker|loudspeaker|gitar|kajon|papan tulis|white board|visi misi/i',
    ];

    public function handle(): int
    {
        $categories = KategoriBarang::pluck('id', 'nama_kategori')->toArray();
        $categoryNames = array_flip($categories);

        foreach (array_keys(self::RULES) as $name) {
            if (! isset($categories[$name])) {
                $this->error("Kategori '{$name}' tidak ditemukan di database.");

                return self::FAILURE;
            }
        }

        $stats = [];
        $skipped = 0;
        $updated = 0;

        DB::transaction(function () use ($categories, $categoryNames, &$stats, &$skipped, &$updated) {
            Barang::orderBy('id')->chunkById(200, function ($barangs) use ($categories, $categoryNames, &$stats, &$skipped, &$updated) {
                foreach ($barangs as $barang) {
                    $kategoriId = $this->guessKategoriId($barang->nama_fasilitas, $categories);

                    if ($kategoriId === null) {
                        $skipped++;
                    } else {
                        $nama = $categoryNames[$kategoriId] ?? (string) $kategoriId;
                        $stats[$nama] = ($stats[$nama] ?? 0) + 1;
                    }

                    if ($barang->kategori_id !== $kategoriId) {
                        $barang->kategori_id = $kategoriId;
                        $barang->save();
                        $updated++;
                    }
                }
            });
        });

        $this->info('Selesai. ' . $updated . ' barang diperbarui, ' . $skipped . ' barang tanpa kategori.');

        foreach ($stats as $nama => $count) {
            $this->line(sprintf('  %-38s %d', $nama, $count));
        }

        return self::SUCCESS;
    }

    private function guessKategoriId(string $namaFasilitas, array $categories): ?int
    {
        if (preg_match(self::SKIP_PATTERN, $namaFasilitas)) {
            return null;
        }

        foreach (self::RULES as $nama => $pattern) {
            if (preg_match($pattern, $namaFasilitas)) {
                return $categories[$nama];
            }
        }

        return null;
    }
}
