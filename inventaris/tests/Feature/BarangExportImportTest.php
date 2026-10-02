<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\KategoriBarang;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BarangExportImportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->userWithInventoryAccess();
    }

    private function csvFile(string $content, string $name = 'barang.csv'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $content);
    }

    public function test_export_csv_returns_expected_columns_and_rows(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Lab A']);
        $kategori = KategoriBarang::create(['nama_kategori' => 'Elektronik']);

        Barang::create([
            'ruangan_id' => $ruangan->id,
            'kategori_id' => $kategori->id,
            'nama_fasilitas' => 'Komputer',
            'kode_inventaris' => 'INV-LABA-001',
            'kondisi' => 'Baik',
            'tahun_pembelian' => 2020,
        ]);

        $response = $this->actingAs($this->user)->get(route('barang.export', ['format' => 'csv']));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $expectedBase = 'data_inventaris_'.now()->locale('id')->translatedFormat('F').now()->format('Y');
        $this->assertStringContainsString($expectedBase.'.csv', $response->headers->get('Content-Disposition'));

        $body = $response->streamedContent();
        $this->assertStringContainsString('kode_inventaris', $body);
        $this->assertStringContainsString('INV-LABA-001', $body);
        $this->assertStringContainsString('Lab A', $body);
        $this->assertStringContainsString('Elektronik', $body);
    }

    public function test_export_word_returns_docx_document(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Lab A']);

        Barang::create([
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Komputer',
            'kode_inventaris' => 'INV-LABA-001',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($this->user)->get(route('barang.export', ['format' => 'word']));

        $response->assertStatus(200);
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            $response->headers->get('Content-Type'),
        );
        $expectedBase = 'data_inventaris_'.now()->locale('id')->translatedFormat('F').now()->format('Y');
        $this->assertStringContainsString($expectedBase.'.docx', $response->headers->get('Content-Disposition'));

        // File .docx adalah arsip ZIP yang diawali magic bytes "PK".
        $this->assertStringStartsWith('PK', $response->getContent());

        $zipPath = tempnam(sys_get_temp_dir(), 'docx');
        file_put_contents($zipPath, $response->getContent());

        $zip = new \ZipArchive;
        $zip->open($zipPath);

        $this->assertNotFalse($zip->locateName('word/media/logo-sttni.png'), 'Logo STTNI harus tertanam di dalam .docx.');

        $document = (string) $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($zipPath);

        $this->assertStringContainsString('INVENTARIS', $document);
        $this->assertStringContainsString('STTNI', $document);
        $this->assertStringContainsString('<w:color w:val="2563EB"/>', $document);
        $this->assertStringContainsString('<w:color w:val="111827"/>', $document);
        $this->assertStringContainsString('r:embed="rIdLogo"', $document);

        $previous = libxml_use_internal_errors(true);
        $xml = new \DOMDocument;
        $this->assertTrue($xml->loadXML($document), 'word/document.xml harus berupa XML yang valid.');
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
    }

    public function test_import_template_returns_csv_header(): void
    {
        $response = $this->actingAs($this->user)->get(route('barang.import.template'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));

        $body = $response->streamedContent();
        $this->assertStringContainsString('kode_inventaris', $body);
        $this->assertStringContainsString('nama_fasilitas', $body);
        $this->assertStringContainsString(';', $body);
    }

    public function test_import_csv_creates_new_barang(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Lab A']);
        $kategori = KategoriBarang::create(['nama_kategori' => 'Elektronik']);

        $csv = "kode_inventaris,nama_fasilitas,ruangan,kategori,kondisi,tahun_pembelian,keterangan\n"
            ."INV-LABA-001,PC Client,Lab A,Elektronik,Baik,2021,PC baru\n";

        $response = $this->actingAs($this->user)->post(route('barang.import'), [
            'file' => $this->csvFile($csv),
            'import_mode' => 'skip',
        ]);

        $response->assertRedirect(route('barang.index'));
        $this->assertDatabaseHas('barang', [
            'kode_inventaris' => 'INV-LABA-001',
            'nama_fasilitas' => 'PC Client',
            'ruangan_id' => $ruangan->id,
            'kategori_id' => $kategori->id,
            'kondisi' => 'Baik',
            'tahun_pembelian' => 2021,
        ]);
    }

    public function test_import_generates_kode_when_blank(): void
    {
        Ruangan::create(['nama_ruangan' => 'Lab A']);

        $csv = "kode_inventaris,nama_fasilitas,ruangan,kategori,kondisi,tahun_pembelian,keterangan\n"
            .",PC Client,Lab A,,Baik,,\n"
            .",PC Client,Lab A,,Baik,,\n";

        $response = $this->actingAs($this->user)->post(route('barang.import'), [
            'file' => $this->csvFile($csv),
            'import_mode' => 'skip',
        ]);

        $response->assertRedirect(route('barang.index'));
        $this->assertDatabaseHas('barang', ['kode_inventaris' => 'INV-LABA-001']);
        $this->assertDatabaseHas('barang', ['kode_inventaris' => 'INV-LABA-002']);
    }

    public function test_import_suggests_closest_ruangan_on_typo(): void
    {
        Ruangan::create(['nama_ruangan' => 'Ruang Ketua']);

        $csv = "kode_inventaris,nama_fasilitas,ruangan,kategori,kondisi,tahun_pembelian,keterangan\n"
            ."INV-X-001,Meja Kerja,Ruang Ketuaa,,Baik,,\n";

        $response = $this->actingAs($this->user)->post(route('barang.import'), [
            'file' => $this->csvFile($csv),
            'import_mode' => 'skip',
        ]);

        $response->assertRedirect(route('barang.import.form'));
        $this->assertStringContainsString('Mungkin maksud Anda "Ruang Ketua"', session('error'));
        $this->assertDatabaseCount('barang', 0);
    }

    public function test_import_matches_ruangan_ignoring_case_and_extra_spaces(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Ruang  Ketua']);

        $csv = "kode_inventaris,nama_fasilitas,ruangan,kategori,kondisi,tahun_pembelian,keterangan\n"
            ."INV-X-001,Meja Kerja,ruang ketua,,Baik,,\n";

        $this->actingAs($this->user)->post(route('barang.import'), [
            'file' => $this->csvFile($csv),
            'import_mode' => 'skip',
        ])->assertRedirect(route('barang.index'));

        $this->assertDatabaseHas('barang', [
            'kode_inventaris' => 'INV-X-001',
            'ruangan_id' => $ruangan->id,
        ]);
    }

    public function test_import_fails_when_ruangan_not_found(): void
    {
        $csv = "kode_inventaris,nama_fasilitas,ruangan,kategori,kondisi,tahun_pembelian,keterangan\n"
            ."INV-X-001,PC Client,Ruangan Hantu,,Baik,,\n";

        $response = $this->actingAs($this->user)->post(route('barang.import'), [
            'file' => $this->csvFile($csv),
            'import_mode' => 'skip',
        ]);

        $response->assertRedirect(route('barang.import.form'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('barang', 0);
    }

    public function test_import_skip_mode_does_not_overwrite_existing(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Lab A']);

        Barang::create([
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Nama Lama',
            'kode_inventaris' => 'INV-LABA-001',
            'kondisi' => 'Baik',
        ]);

        $csv = "kode_inventaris,nama_fasilitas,ruangan,kategori,kondisi,tahun_pembelian,keterangan\n"
            ."INV-LABA-001,Nama Baru,Lab A,,Rusak,,\n";

        $this->actingAs($this->user)->post(route('barang.import'), [
            'file' => $this->csvFile($csv),
            'import_mode' => 'skip',
        ])->assertRedirect(route('barang.index'));

        $this->assertDatabaseHas('barang', [
            'kode_inventaris' => 'INV-LABA-001',
            'nama_fasilitas' => 'Nama Lama',
            'kondisi' => 'Baik',
        ]);
    }

    public function test_import_replace_mode_updates_existing(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Lab A']);

        Barang::create([
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Nama Lama',
            'kode_inventaris' => 'INV-LABA-001',
            'kondisi' => 'Baik',
        ]);

        $csv = "kode_inventaris,nama_fasilitas,ruangan,kategori,kondisi,tahun_pembelian,keterangan\n"
            ."INV-LABA-001,Nama Baru,Lab A,,Rusak,,\n";

        $this->actingAs($this->user)->post(route('barang.import'), [
            'file' => $this->csvFile($csv),
            'import_mode' => 'replace',
        ])->assertRedirect(route('barang.index'));

        $this->assertDatabaseHas('barang', [
            'kode_inventaris' => 'INV-LABA-001',
            'nama_fasilitas' => 'Nama Baru',
            'kondisi' => 'Rusak',
        ]);
    }
}
