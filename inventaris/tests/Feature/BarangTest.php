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
            'jumlah' => 1,
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
            'jumlah' => 1,
            'kode_inventaris' => 'INV-001',
            'kondisi' => 'Baik',
        ]);

        Barang::create([
            'ruangan_id' => $ruangan->id,
            'nama_fasilitas' => 'Kursi Kayu',
            'jumlah' => 1,
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
}
