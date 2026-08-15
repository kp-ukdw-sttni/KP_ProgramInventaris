<?php

namespace App\Console\Commands;

use App\Models\Barang;
use App\Models\Ruangan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InventoryImportWord extends Command
{
    protected $signature = 'inventory:import-word
        {file? : Path ke file .docx sumber data}
        {--wipe : Hapus seluruh data barang lama sebelum import}';

    protected $description = 'Import inventaris dari dokumen Word daftar sarana & prasarana STTNI.';

    private const W_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /**
     * Peta nama ruangan di Word -> nama ruangan di database.
     */
    private const ROOM_MAP = [
        'Ruang Kelas A' => 'Ruang Kelas A',
        'Ruang Kelas B' => 'Ruang Kelas B',
        'Ruang Kelas C' => 'Ruang Kelas C',
        'Ruang Kelas D' => 'Ruang Kelas D',
        'Ruang Kelas E' => 'Ruang Kelas E',
        'Ruang Kelas Pasca Sarjana (?)' => 'Ruang Kelas Pasca Sarjana',
        'Ruang Ketua' => 'Ruang Ketua',
        'Ruang WK I Bidang Akademik' => 'Ruang WK I Bidang Akademik',
        'Ruang Bendahara' => 'Ruang Bendahara',
        'Ruang WK II Bidang Keuangan dan Kepegawaian' => 'Ruang WK II Bidang Keuangan',
        'Ruang ADAK' => 'Ruang ADAK',
        'Ruang Kaprodi Teologi' => 'Ruang Kaprodi Teologi',
        'Ruang Sekretaris Prodi Teologi' => 'Ruang Sekretaris Prodi Teologi',
        'Ruang Kaprodi PAK' => 'Ruang Kaprodi PAK',
        'Ruang Sekretaris Prodi PAK' => 'Ruang Sekretaris Prodi PAK',
        'Ruang Sekretaris Pasca Sarjana (Dan WK III)' => 'Ruang Sekretaris Pasca Sarjana',
        'Ruang Sekretaris Umum' => 'Ruang Sekretaris Umum',
        'Ruang Tamu' => 'Ruang Tamu',
        'Gedung Perpustakaan' => 'Gedung Perpustakaan',
        'Laboratorium Komputer' => 'Laboratorium Komputer',
        'Gedung Kapel' => 'Gedung Kapel',
        'Gedung Asrama Putra' => 'Gedung Asrama Putra',
        'Gedung Asrama Putri' => 'Gedung Asrama Putri',
        'Lobby Utama' => 'Lobby Utama',
        'Pos Keamanan' => 'Pos Keamanan',
        'Gudang' => 'Gudang',
        'Wisma Tamu' => 'Wisma Tamu',
        'Lapangan Olah Raga' => 'Lapangan Olah Raga',
        'Kebun Buah' => 'Kebun Buah',
        'Kolam Ikan Buatan' => 'Kolam Ikan Buatan',
        'Kolam Ikan Alami' => 'Kolam Ikan Alami',
        'Ruangan Promosi (Ruang BEM / Ruang pak Hadi)' => 'Ruangan Promosi / BEM',
        'Luar Ruangan' => 'Luar Ruangan',
        'Dapur' => 'Dapur',
    ];

    public function handle(): int
    {
        $file = $this->argument('file') ?? base_path('daftar inventaris sttni - (baru).docx');

        if (! file_exists($file)) {
            $this->error("File tidak ditemukan: {$file}");

            return self::FAILURE;
        }

        if ($this->option('wipe')) {
            $this->backupExistingData();
        }

        $rows = $this->extractRows($file);

        if ($rows === null) {
            return self::FAILURE;
        }

        if (count($rows) === 0) {
            $this->error('Tidak ada baris data yang ditemukan di dokumen.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($rows) {
            if ($this->option('wipe')) {
                Barang::query()->delete();
                $this->info('Data barang lama dihapus.');
            }

            $rooms = [];
            $noYear = [];
            $nonNumericJumlah = [];
            $totalUnits = 0;

            foreach ($rows as $row) {
                $roomName = $row['ruangan'] ?? null;

                if (! $roomName || ! isset(self::ROOM_MAP[$roomName])) {
                    $this->warn("Barang '{$row['nama']}' dilewati: ruangan '{$roomName}' tidak dikenal.");

                    continue;
                }

                $dbRoomName = self::ROOM_MAP[$roomName];

                if (! isset($rooms[$dbRoomName])) {
                    $rooms[$dbRoomName] = Ruangan::firstOrCreate(
                        ['nama_ruangan' => $dbRoomName],
                        ['deskripsi' => 'Dibuat otomatis dari import dokumen Word.']
                    );
                }

                $ruangan = $rooms[$dbRoomName];

                $nama = trim($row['nama']);
                $jumlahRaw = trim($row['jumlah']);
                $keterangan = trim($row['keterangan']);
                $kondisi = $this->normalizeKondisi($row['kondisi']);
                $tahun = $this->parseTahun($keterangan);

                $jumlah = $this->parseJumlah($jumlahRaw);

                if (! $jumlah) {
                    $nonNumericJumlah[] = "{$nama} ({$jumlahRaw})";
                    $jumlah = 1;
                }

                if ($tahun === null) {
                    $noYear[] = $nama;
                }

                $prefix = Barang::kodePrefix($ruangan);
                $seq = Barang::nextSequence($ruangan);

                for ($i = 1; $i <= $jumlah; $i++) {
                    Barang::create([
                        'ruangan_id' => $ruangan->id,
                        'kategori_id' => null,
                        'nama_fasilitas' => $jumlah > 1 ? $nama.' '.$i : $nama,
                        'kode_inventaris' => Barang::formatKode($prefix, $seq++),
                        'tahun_pembelian' => $tahun,
                        'kondisi' => $kondisi,
                        'keterangan' => $keterangan,
                    ]);

                    $totalUnits++;
                }
            }

            $this->info("Selesai. Total {$totalUnits} unit barang diimport ke " . count($rooms) . ' ruangan.');

            if (count($nonNumericJumlah) > 0) {
                $this->warn('Jumlah non-numerik (dianggap 1 unit):');
                foreach ($nonNumericJumlah as $item) {
                    $this->line('  - '.$item);
                }
            }

            if (count($noYear) > 0) {
                $this->warn(count($noYear).' item tanpa tahun pembelian (tahun_pembelian kosong):');
                foreach (array_unique($noYear) as $item) {
                    $this->line('  - '.$item);
                }
            }
        });

        return self::SUCCESS;
    }

    /**
     * Ekstrak baris data (ruangan, nama, jumlah, keterangan, kondisi) dari .docx.
     */
    private function extractRows(string $file): ?array
    {
        $zip = new \ZipArchive();

        if ($zip->open($file) !== true) {
            $this->error('Gagal membuka file .docx (bukan arsip ZIP yang valid).');

            return null;
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            $this->error('word/document.xml tidak ditemukan di dalam file.');

            return null;
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadXML($xml);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', self::W_NS);

        $body = $xpath->query('//w:body')->item(0);

        if (! $body) {
            $this->error('Struktur dokumen tidak valid.');

            return null;
        }

        $rows = [];
        $currentRoom = null;

        foreach ($body->childNodes as $child) {
            if (! $child instanceof \DOMElement) {
                continue;
            }

            if ($child->localName === 'p') {
                $text = trim($this->paragraphText($xpath, $child));

                if ($text !== '') {
                    $currentRoom = $text;
                }

                continue;
            }

            if ($child->localName !== 'tbl') {
                continue;
            }

            $trs = $xpath->query('./w:tr', $child);

            foreach ($trs as $rowIndex => $tr) {
                if ($rowIndex === 0) {
                    continue; // baris header
                }

                $cells = $xpath->query('./w:tc', $tr);
                $cols = [];

                foreach ($cells as $tc) {
                    $cols[] = trim($this->cellText($xpath, $tc));
                }

                $cols = array_pad($cols, 5, '');

                $nama = trim($cols[1] ?? '');

                if ($nama === '' || preg_match('/^luas\b/i', $nama)) {
                    continue;
                }

                $rows[] = [
                    'ruangan' => $currentRoom,
                    'nama' => $nama,
                    'jumlah' => $cols[2] ?? '',
                    'keterangan' => $cols[3] ?? '',
                    'kondisi' => $cols[4] ?? '',
                ];
            }
        }

        return $rows;
    }

    private function paragraphText(\DOMXPath $xpath, \DOMElement $p): string
    {
        $texts = $xpath->query('.//w:t', $p);
        $parts = [];

        foreach ($texts as $t) {
            $parts[] = $t->textContent;
        }

        return implode('', $parts);
    }

    private function cellText(\DOMXPath $xpath, \DOMElement $tc): string
    {
        $paras = $xpath->query('.//w:p', $tc);
        $lines = [];

        foreach ($paras as $p) {
            $lines[] = $this->paragraphText($xpath, $p);
        }

        return implode(' ', $lines);
    }

    /**
     * Ambil angka 4 digit terakhir yang muncul di keterangan sebagai tahun pembelian.
     */
    private function parseTahun(string $keterangan): ?int
    {
        if ($keterangan === '') {
            return null;
        }

        if (preg_match_all('/\b(\d{4})\b/', $keterangan, $matches)) {
            return (int) end($matches[1]);
        }

        return null;
    }

    /**
     * Jumlah unit: angka murni -> jumlahnya; selain itu (termasuk kosong / "-") -> 1.
     */
    private function parseJumlah(string $raw): ?int
    {
        $raw = trim($raw);

        if ($raw === '' || $raw === '-' || $raw === '–') {
            return 1;
        }

        if (ctype_digit($raw)) {
            return (int) $raw;
        }

        return null;
    }

    /**
     * Normalisasi keterangan kondisi bebas di Word ke enum database.
     */
    private function normalizeKondisi(string $raw): string
    {
        $value = mb_strtolower(trim($raw));

        if (str_contains($value, 'mati')) {
            return 'Mati';
        }

        if (str_contains($value, 'rusak')) {
            return 'Rusak';
        }

        if (str_contains($value, 'kurang baik')
            || str_contains($value, 'kurangbaik')
            || str_contains($value, 'kuran baik')
            || str_contains($value, 'pengecatan')) {
            return 'Kurang Baik';
        }

        return 'Baik';
    }

    /**
     * Backup data barang lama ke storage/app sebelum dihapus.
     */
    private function backupExistingData(): void
    {
        $count = Barang::count();

        if ($count === 0) {
            return;
        }

        $backupFile = storage_path('app/backup_barang_'.now()->format('Ymd_His').'.json');
        file_put_contents($backupFile, Barang::with(['ruangan', 'kategoriBarang'])->get()->toJson(JSON_PRETTY_PRINT));

        $this->info("Backup {$count} barang lama -> {$backupFile}");
    }
}
