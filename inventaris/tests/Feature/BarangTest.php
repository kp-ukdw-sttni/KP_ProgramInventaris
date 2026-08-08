<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ruangan;
use App\Models\KategoriBarang;
use App\Models\Barang;

class BarangTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test guest user must be redirected to login.
     */
    public function test_must_be_authenticated_to_view_barang(): void
    {
        $response = $this->get(route('barang.index'));
        $response->assertRedirect('/login');
    }

    /**
     * Test normal request returns the full index page.
     */
    public function test_index_displays_correct_view_for_non_ajax_requests(): void
    {
        $response = $this->actingAs($this->user)->get(route('barang.index'));
        $response->assertStatus(200);
        $response->assertViewIs('barang.index');
        $response->assertSee('id="searchInput"', false);
    }

    /**
     * Test AJAX request returns only the partial table rows.
     */
    public function test_index_displays_partial_view_for_ajax_requests(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Lab A']);
        Barang::create([
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Spesifik Item',
            'kode_inventaris' => 'INV-001',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($this->user)->get(route('barang.index'), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(200);
        $response->assertDontSee('<!DOCTYPE html>');
        $response->assertSee('Spesifik Item');
        $response->assertSee('INV-001');
    }

    /**
     * Test AJAX search filters results correctly.
     */
    public function test_ajax_search_filters_results(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Lab A']);

        Barang::create([
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Monitor Dell',
            'kode_inventaris' => 'INV-001',
            'kondisi' => 'Baik',
        ]);

        Barang::create([
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Kursi Kayu',
            'kode_inventaris' => 'INV-002',
            'kondisi' => 'Baik',
        ]);

        // Search for "Monitor"
        $response = $this->actingAs($this->user)->get(route('barang.index', ['search' => 'Monitor']), [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertStatus(200);
        $response->assertSee('Monitor Dell');
        $response->assertDontSee('Kursi Kayu');
    }

    /**
     * Test storing with jumlah > 1 creates one record per unit, each
     * with its own unique, auto-generated kode inventaris.
     */
    public function test_store_creates_one_record_per_unit_with_unique_kode(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Laboratorium Komputer']);
        $kategori = KategoriBarang::create(['nama_kategori' => 'Elektronik']);

        $response = $this->actingAs($this->user)->post(route('barang.store'), [
            'ruangan_id' => $ruangan->id,
            'kategori_id' => $kategori->id,
            'nama_fasilitas' => 'PC Client',
            'jumlah' => 3,
            'kondisi' => 'Baik',
            'keterangan' => 'Unit lab',
        ]);

        $response->assertRedirect(route('barang.index'));
        $this->assertDatabaseCount('barang', 3);

        $barangs = Barang::orderBy('kode_inventaris')->get();

        $this->assertEquals(
            ['INV-LABORATORIUM-001', 'INV-LABORATORIUM-002', 'INV-LABORATORIUM-003'],
            $barangs->pluck('kode_inventaris')->all()
        );
        $this->assertEquals(
            ['PC Client 1', 'PC Client 2', 'PC Client 3'],
            $barangs->pluck('nama_fasilitas')->all()
        );
    }

    /**
     * Test storing a single unit keeps the original name and gets kode 001.
     */
    public function test_store_single_unit_keeps_original_name(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Ruang Ketua']);

        $this->actingAs($this->user)->post(route('barang.store'), [
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Meja Kerja Eksekutif',
            'jumlah' => 1,
            'kondisi' => 'Baik',
        ]);

        $this->assertDatabaseHas('barang', [
            'nama_fasilitas' => 'Meja Kerja Eksekutif',
            'kode_inventaris' => 'INV-RUANGKETUA-001',
        ]);
    }

    /**
     * Test generating a new kode continues the sequence after existing codes.
     */
    public function test_generate_kode_continues_existing_sequence(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Gedung Kapel']);

        Barang::create([
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Speaker',
            'kode_inventaris' => 'INV-GEDUNGKAPEL-005',
            'kondisi' => 'Baik',
        ]);

        $this->assertSame(
            'INV-GEDUNGKAPEL-006',
            Barang::generateKodeInventaris($ruangan)
        );
    }
}
