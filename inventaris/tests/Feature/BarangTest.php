<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Concerns\AssertsListStateLinks;
use App\Models\User;
use App\Models\Ruangan;
use App\Models\KategoriBarang;
use App\Models\Barang;

class BarangTest extends TestCase
{
    use RefreshDatabase, AssertsListStateLinks;

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
            'ruangan_ids' => [$ruangan->id],
            'kategori_id' => $kategori->id,
            'nama_fasilitas' => 'PC Client',
            'jumlah' => 3,
            'kondisi' => 'Baik',
            'keterangan' => 'Unit lab',
        ]);

        $response->assertRedirect(route('barang.index'));
        $response->assertSessionHas(
            'success',
            '3 unit PC Client berhasil ditambahkan di Laboratorium Komputer (kode INV-LABORATORIUMKOMPUTER-001 s.d. INV-LABORATORIUMKOMPUTER-003).'
        );
        $this->assertDatabaseCount('barang', 3);

        $barangs = Barang::orderBy('kode_inventaris')->get();

        $this->assertEquals(
            ['INV-LABORATORIUMKOMPUTER-001', 'INV-LABORATORIUMKOMPUTER-002', 'INV-LABORATORIUMKOMPUTER-003'],
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

        $response = $this->actingAs($this->user)->post(route('barang.store'), [
            'ruangan_ids' => [$ruangan->id],
            'nama_fasilitas' => 'Meja Kerja Eksekutif',
            'jumlah' => 1,
            'kondisi' => 'Baik',
        ]);

        $response->assertSessionHas(
            'success',
            '1 unit Meja Kerja Eksekutif berhasil ditambahkan di Ruang Ketua (kode INV-RUANGKETUA-001).'
        );

        $this->assertDatabaseHas('barang', [
            'nama_fasilitas' => 'Meja Kerja Eksekutif',
            'kode_inventaris' => 'INV-RUANGKETUA-001',
        ]);
    }

    /**
     * Test storing into several rooms at once spreads the units and reports
     * the per-room breakdown in the flash notification.
     */
    public function test_store_across_multiple_rooms_creates_one_record_per_unit(): void
    {
        $lab = Ruangan::create(['nama_ruangan' => 'Lab A']);
        $kantor = Ruangan::create(['nama_ruangan' => 'Ruang Bendahara']);

        $response = $this->actingAs($this->user)->post(route('barang.store'), [
            'ruangan_ids' => [$lab->id, $kantor->id],
            'nama_fasilitas' => 'Kursi Lipat',
            'jumlah' => 2,
            'kondisi' => 'Baik',
        ]);

        $response->assertSessionHas(
            'success',
            '4 unit Kursi Lipat berhasil ditambahkan di 2 ruangan: Lab A (2 unit), Ruang Bendahara (2 unit).'
        );

        $this->assertDatabaseCount('barang', 4);
        $this->assertSame(2, Barang::where('ruangan_id', $lab->id)->count());
        $this->assertSame(2, Barang::where('ruangan_id', $kantor->id)->count());
        $this->assertEquals(
            ['INV-LABA-001', 'INV-LABA-002', 'INV-RUANGBENDAHARA-001', 'INV-RUANGBENDAHARA-002'],
            Barang::orderBy('id')->pluck('kode_inventaris')->all()
        );
    }

    /**
     * Test updating a flash notification names the item and its inventory code.
     */
    public function test_update_reports_item_name_and_kode_in_flash(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Gedung Kapel']);
        $barang = Barang::create([
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Speaker',
            'kode_inventaris' => 'INV-GEDUNGKAPEL-001',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($this->user)->put(route('barang.update', $barang->id), [
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Speaker Aktif',
            'kondisi' => 'Rusak',
        ]);

        $response = $this->actingAs($this->user)->put(route('barang.update', $barang->id), [
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Speaker Aktif',
            'kondisi' => 'Rusak',
        ]);

        $response->assertRedirect(route('barang.index'));
        $response->assertSessionHas(
            'success',
            'Barang "Speaker Aktif" (INV-GEDUNGKAPEL-001) berhasil diperbarui.'
        );
    }

    /**
     * Test preserving search filter across store, update, and destroy.
     */
    public function test_list_filters_are_preserved_after_crud(): void
    {
        $lab = Ruangan::create(['nama_ruangan' => 'Lab A']);
        $lab2 = Ruangan::create(['nama_ruangan' => 'Lab B']);
        $kat = KategoriBarang::create(['nama_kategori' => 'Elektronik']);

        // Item that matches search
        $matching = Barang::create([
            'ruangan_id' => $lab->id,
            'kategori_id' => $kat->id,
            'nama_fasilitas' => 'Monitor XYZ',
            'kode_inventaris' => 'INV-LABA-001',
            'kondisi' => 'Baik',
        ]);
        Barang::create([
            'ruangan_id' => $lab2->id,
            'nama_fasilitas' => 'Kursi',
            'kode_inventaris' => 'INV-LABB-001',
            'kondisi' => 'Baik',
        ]);

        $query = [
            'search' => 'Monitor',
            'ruangan_id' => $lab->id,
            'kategori_id' => $kat->id,
            'kondisi' => 'Baik',
            'page' => 1,
        ];

        // Store should redirect back with filters (and reset page)
        $store = $this->actingAs($this->user)->post(route('barang.store', $query), [
            'ruangan_ids' => [$lab->id],
            'kategori_id' => $kat->id,
            'nama_fasilitas' => 'Mouse',
            'jumlah' => 1,
            'kondisi' => 'Baik',
        ]);
        $store->assertRedirect(route('barang.index', array_merge($query, ['page' => null])));

        // Update should preserve all filters (including page)
        $update = $this->actingAs($this->user)->put(route('barang.update', array_merge([$matching->id], $query)), [
            'ruangan_id' => $lab->id,
            'kategori_id' => $kat->id,
            'nama_fasilitas' => 'Monitor XYZ',
            'kondisi' => 'Kurang Baik',
        ]);
        $update->assertRedirect(route('barang.index', $query));

        // Destroy should preserve filters and clamp page if needed (kept same here)
        $destroy = $this->actingAs($this->user)->delete(route('barang.destroy', array_merge([$matching->id], $query)));
        $destroy->assertRedirect(route('barang.index', $query));
    }

    /**
     * Test the list view keeps the active query string on its action links so
     * filters survive the trip to the edit form and back.
     */
    public function test_index_action_links_carry_the_active_query_string(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Lab A']);

        // 16 unit yang cocok supaya halaman 2 benar-benar punya isi
        foreach (range(1, 16) as $i) {
            Barang::create([
                'ruangan_id' => $ruangan->id,
                'nama_fasilitas' => 'Monitor '.$i,
                'kode_inventaris' => 'INV-LABA-'.str_pad($i, 3, '0', STR_PAD_LEFT),
                'kondisi' => 'Baik',
            ]);
        }

        $barang = Barang::orderByDesc('id')->first();
        $query = ['search' => 'Monitor', 'ruangan_id' => $ruangan->id, 'page' => 2];

        $response = $this->actingAs($this->user)->get(route('barang.index', $query));

        $response->assertStatus(200);

        $params = [
            'search' => 'Monitor',
            'ruangan_id' => $ruangan->id,
            'page' => 2,
        ];

        // Link "Tambah Barang" menuju form create sambil menjaga filter
        $this->assertUrlCarries($response, route('barang.create'), $params);

        // Link edit dan form hapus di dalam partial tabel ikut membawa filter
        $this->assertUrlCarries($response, route('barang.edit', $barang->id), $params);
        $this->assertUrlCarries($response, route('barang.destroy', $barang->id), $params);
    }

    /**
     * Test the destroy flash notification names the room the item came from.
     */
    public function test_destroy_reports_room_in_flash(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Gudang']);
        $barang = Barang::create([
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Gerobak Sorong',
            'kode_inventaris' => 'INV-GUDANG-001',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($this->user)->delete(route('barang.destroy', $barang->id));

        $response->assertSessionHas(
            'success',
            '1 unit barang "Gerobak Sorong" (INV-GUDANG-001) berhasil dihapus dari Gudang.'
        );
        $this->assertDatabaseCount('barang', 0);
    }

    /**
     * Test the flash message is rendered as a toast inside the page layout.
     */
    public function test_flash_message_renders_as_a_toast_notification(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Pos Keamanan']);

        $response = $this->actingAs($this->user)->post(route('barang.store'), [
            'ruangan_ids' => [$ruangan->id],
            'nama_fasilitas' => 'CCTV',
            'jumlah' => 1,
            'kondisi' => 'Baik',
        ]);

        $response->assertRedirect(route('barang.index'));
        $response->assertSessionHas('success');

        session()->flash('success', '1 unit CCTV berhasil ditambahkan di Pos Keamanan.');

        $this->actingAs($this->user)
            ->get(route('barang.index'))
            ->assertStatus(200)
            ->assertSee('toast-progress', false)
            ->assertSee('1 unit CCTV berhasil ditambahkan di Pos Keamanan.');
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
