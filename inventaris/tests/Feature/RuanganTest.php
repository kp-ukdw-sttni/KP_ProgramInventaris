<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Concerns\AssertsListStateLinks;
use App\Models\User;
use App\Models\Ruangan;
use App\Models\KategoriBarang;
use App\Models\Barang;

class RuanganTest extends TestCase
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
    public function test_must_be_authenticated_to_view_ruangan(): void
    {
        $response = $this->get(route('ruangan.index'));
        $response->assertRedirect('/login');
    }

    /**
     * Test show page displays the room name and total asset count.
     */
    public function test_show_displays_room_name_and_total(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Ruang Kelas A']);
        Barang::create([
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Kursi 1',
            'kode_inventaris' => 'INV-001',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($this->user)->get(route('ruangan.show', $ruangan->id));
        $response->assertStatus(200);
        $response->assertViewIs('ruangan.show');
        $response->assertSee('Ruang Kelas A');
        $response->assertSee('1 barang');
    }

    /**
     * Test show page groups assets per category and per item type.
     */
    public function test_show_displays_per_category_breakdown(): void
    {
        $ruangan = Ruangan::create(['nama_ruangan' => 'Gudang Utama']);
        $meja = KategoriBarang::create(['nama_kategori' => 'Mebel']);
        $elektronik = KategoriBarang::create(['nama_kategori' => 'Elektronik']);

        Barang::create([
            'ruangan_id' => $ruangan->id,
            'kategori_id' => $meja->id,
            'nama_fasilitas' => 'Meja 1',
            'kode_inventaris' => 'INV-001',
            'kondisi' => 'Baik',
        ]);
        Barang::create([
            'ruangan_id' => $ruangan->id,
            'kategori_id' => $meja->id,
            'nama_fasilitas' => 'Meja 2',
            'kode_inventaris' => 'INV-002',
            'kondisi' => 'Baik',
        ]);
        Barang::create([
            'ruangan_id' => $ruangan->id,
            'kategori_id' => $elektronik->id,
            'nama_fasilitas' => 'Monitor',
            'kode_inventaris' => 'INV-003',
            'kondisi' => 'Baik',
        ]);
        Barang::create([
            'ruangan_id' => $ruangan->id,
            'kategori_id' => null,
            'nama_fasilitas' => 'Rak Besi',
            'kode_inventaris' => 'INV-004',
            'kondisi' => 'Baik',
        ]);

        $response = $this->actingAs($this->user)->get(route('ruangan.show', $ruangan->id));
        $response->assertStatus(200);
        $response->assertSee('4 barang');
        $response->assertSee('Mebel');
        $response->assertSee('Elektronik');
        $response->assertSee('Tanpa Kategori');

        $response->assertSee('Meja');
        $response->assertSee('2 barang');
    }

    /**
     * Test the page query is preserved after store, update, and destroy.
     */
    public function test_page_query_is_preserved_after_crud(): void
    {
        // 20 ruangan supaya halaman 2 masih ada isinya (15 per halaman)
        for ($i = 1; $i <= 20; $i++) {
            Ruangan::create(['nama_ruangan' => 'Ruang ' . $i]);
        }

        $ruangan = Ruangan::where('nama_ruangan', 'Ruang 20')->firstOrFail();
        $query = ['page' => 2];

        $store = $this->actingAs($this->user)->post(route('ruangan.store', $query), [
            'nama_ruangan' => 'Ruang Musik',
            'deskripsi' => 'Latihan',
        ]);
        $store->assertRedirect(route('ruangan.index', $query));

        $update = $this->actingAs($this->user)->put(route('ruangan.update', array_merge([$ruangan->id], $query)), [
            'nama_ruangan' => 'Ruang Musik Utama',
            'deskripsi' => null,
        ]);
        $update->assertRedirect(route('ruangan.index', $query));

        $destroy = $this->actingAs($this->user)->delete(route('ruangan.destroy', array_merge([$ruangan->id], $query)));
        $destroy->assertRedirect(route('ruangan.index', $query));
    }

    /**
     * Test destroy clamps the page when the last row of the final page is removed.
     */
    public function test_destroy_clamps_page_when_last_row_is_deleted(): void
    {
        for ($i = 1; $i <= 16; $i++) {
            Ruangan::create(['nama_ruangan' => 'Ruang ' . $i]);
        }

        // Baris ke-16 adalah satu-satunya isi halaman 2
        $terakhir = Ruangan::where('nama_ruangan', 'Ruang 16')->firstOrFail();

        $response = $this->actingAs($this->user)->delete(
            route('ruangan.destroy', [$terakhir->id, 'page' => 2])
        );

        // 15 ruangan tersisa = 1 halaman, jadi kembali ke halaman 1
        $response->assertRedirect(route('ruangan.index'));
    }

    /**
     * Test the list view keeps the active page on its action links.
     */
    public function test_index_action_links_carry_the_active_page(): void
    {
        // 16 ruangan supaya halaman 2 benar-benar punya isi
        for ($i = 1; $i <= 16; $i++) {
            Ruangan::create(['nama_ruangan' => 'Ruang ' . $i]);
        }

        $ruangan = Ruangan::where('nama_ruangan', 'Ruang 16')->firstOrFail();

        $response = $this->actingAs($this->user)->get(route('ruangan.index', ['page' => 2]));

        $response->assertStatus(200);

        $this->assertUrlCarries($response, route('ruangan.create'), ['page' => 2]);
        $this->assertUrlCarries($response, route('ruangan.edit', $ruangan->id), ['page' => 2]);
        $this->assertUrlCarries($response, route('ruangan.destroy', $ruangan->id), ['page' => 2]);
    }
}