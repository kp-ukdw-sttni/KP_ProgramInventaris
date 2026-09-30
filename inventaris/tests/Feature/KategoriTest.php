<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Concerns\AssertsListStateLinks;
use App\Models\User;
use App\Models\KategoriBarang;

class KategoriTest extends TestCase
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
    public function test_must_be_authenticated_to_view_kategori(): void
    {
        $this->get(route('kategori.index'))->assertRedirect('/login');
    }

    /**
     * Test index honours the sort column and direction coming from the query string.
     */
    public function test_index_sorts_by_requested_column_and_direction(): void
    {
        KategoriBarang::create(['nama_kategori' => 'Mebel']);
        KategoriBarang::create(['nama_kategori' => 'Elektronik']);

        $response = $this->actingAs($this->user)
            ->get(route('kategori.index', ['sort' => 'nama_kategori', 'direction' => 'desc']));

        $response->assertStatus(200);
        $response->assertViewIs('kategori.index');
        $response->assertViewHas('column', 'nama_kategori');
        $response->assertViewHas('direction', 'desc');
    }

    /**
     * Test sort and direction are preserved after store, update, and destroy.
     */
    public function test_sort_state_is_preserved_after_crud(): void
    {
        $kategori = KategoriBarang::create(['nama_kategori' => 'Mebel']);
        $query = ['sort' => 'nama_kategori', 'direction' => 'desc', 'page' => 1];

        $store = $this->actingAs($this->user)->post(route('kategori.store', $query), [
            'nama_kategori' => 'ATK',
        ]);
        $store->assertRedirect(route('kategori.index', $query));

        $update = $this->actingAs($this->user)->put(route('kategori.update', array_merge([$kategori->id], $query)), [
            'nama_kategori' => 'Mebel Kayu',
        ]);
        $update->assertRedirect(route('kategori.index', $query));

        $destroy = $this->actingAs($this->user)->delete(route('kategori.destroy', array_merge([$kategori->id], $query)));
        $destroy->assertRedirect(route('kategori.index', $query));
    }

    /**
     * Test unknown sort columns and directions fall back to the safe defaults.
     */
    public function test_invalid_sort_state_falls_back_to_defaults(): void
    {
        KategoriBarang::create(['nama_kategori' => 'Mebel']);

        $response = $this->actingAs($this->user)
            ->get(route('kategori.index', ['sort' => 'nama_fasilitas; DROP TABLE', 'direction' => 'sideways']));

        $response->assertStatus(200);
        $response->assertViewHas('column', 'nama_kategori');
        $response->assertViewHas('direction', 'asc');
    }

    /**
     * Test the list view keeps the active sort on its action links.
     */
    public function test_index_action_links_carry_the_active_sort_state(): void
    {
        $kategori = KategoriBarang::create(['nama_kategori' => 'Mebel']);
        $query = ['sort' => 'nama_kategori', 'direction' => 'desc'];

        $response = $this->actingAs($this->user)->get(route('kategori.index', $query));

        $response->assertStatus(200);

        $params = ['sort' => 'nama_kategori', 'direction' => 'desc'];

        $this->assertUrlCarries($response, route('kategori.create'), $params);
        $this->assertUrlCarries($response, route('kategori.edit', $kategori->id), $params);
        $this->assertUrlCarries($response, route('kategori.destroy', $kategori->id), $params);
    }
}
