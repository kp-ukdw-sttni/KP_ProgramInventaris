<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ruangan;
use App\Models\KategoriBarang;
use App\Models\Barang;

class RuanganTest extends TestCase
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
}