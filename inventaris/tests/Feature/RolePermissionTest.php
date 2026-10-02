<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Ruangan;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staf;

    private User $viewer;

    private Ruangan $ruangan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->admin = User::where('email', 'admin@sarpras.com')->firstOrFail();
        $this->staf = User::where('email', 'staf@sarpras.com')->firstOrFail();
        $this->viewer = User::where('email', 'guest@sarpras.com')->firstOrFail();
        $this->ruangan = Ruangan::create(['nama_ruangan' => 'Lab A']);
    }

    private function barangBaru(): Barang
    {
        return Barang::create([
            'ruangan_id' => $this->ruangan->id,
            'nama_fasilitas' => 'Kursi',
            'kode_inventaris' => 'INV-001',
            'kondisi' => 'Baik',
        ]);
    }

    public function test_staf_can_create_and_edit_barang(): void
    {
        $this->actingAs($this->staf)->post(route('barang.store'), [
            'ruangan_ids' => [$this->ruangan->id],
            'nama_fasilitas' => 'Meja',
            'jumlah' => 1,
            'kondisi' => 'Baik',
        ])->assertRedirect(route('barang.index'));

        $this->assertDatabaseHas('barang', ['nama_fasilitas' => 'Meja']);

        $barang = $this->barangBaru();

        $this->actingAs($this->staf)->put(route('barang.update', $barang), [
            'ruangan_id' => $this->ruangan->id,
            'nama_fasilitas' => 'Kursi Update',
            'kondisi' => 'Rusak',
        ])->assertRedirect(route('barang.index'));

        $this->assertDatabaseHas('barang', ['id' => $barang->id, 'nama_fasilitas' => 'Kursi Update']);
    }

    public function test_staf_can_import_barang(): void
    {
        $this->actingAs($this->staf)->get(route('barang.import.form'))
            ->assertOk()
            ->assertDontSee('value="replace"', false);

        $csv = "kode_inventaris;nama_fasilitas;ruangan;kondisi\nINV-999;Papan Tulis;Lab A;Baik\n";

        $this->actingAs($this->staf)->post(route('barang.import'), [
            'file' => UploadedFile::fake()->createWithContent('barang.csv', $csv),
            'import_mode' => 'skip',
        ])->assertRedirect(route('barang.index'));

        $this->assertDatabaseHas('barang', ['kode_inventaris' => 'INV-999']);
    }

    public function test_staf_cannot_use_replace_import_mode(): void
    {
        $barang = $this->barangBaru();

        $csv = "kode_inventaris;nama_fasilitas;ruangan;kondisi\nINV-001;Kursi Ganti;Lab A;Rusak\n";

        $this->actingAs($this->staf)->post(route('barang.import'), [
            'file' => UploadedFile::fake()->createWithContent('barang.csv', $csv),
            'import_mode' => 'replace',
        ])->assertRedirect(route('barang.import.form'));

        $this->assertDatabaseHas('barang', ['id' => $barang->id, 'nama_fasilitas' => 'Kursi']);
        $this->assertDatabaseMissing('barang', ['nama_fasilitas' => 'Kursi Ganti']);
    }

    public function test_admin_can_use_replace_import_mode(): void
    {
        $barang = $this->barangBaru();

        $this->actingAs($this->admin)->get(route('barang.import.form'))
            ->assertOk()
            ->assertSee('value="replace"', false);

        $csv = "kode_inventaris;nama_fasilitas;ruangan;kondisi\nINV-001;Kursi Ganti;Lab A;Rusak\n";

        $this->actingAs($this->admin)->post(route('barang.import'), [
            'file' => UploadedFile::fake()->createWithContent('barang.csv', $csv),
            'import_mode' => 'replace',
        ])->assertRedirect(route('barang.index'));

        $this->assertDatabaseHas('barang', ['id' => $barang->id, 'nama_fasilitas' => 'Kursi Ganti']);
    }

    public function test_staf_cannot_delete_barang(): void
    {
        $barang = $this->barangBaru();

        $this->actingAs($this->staf)->delete(route('barang.destroy', $barang))->assertForbidden();
        $this->assertDatabaseHas('barang', ['id' => $barang->id]);
    }

    public function test_staf_cannot_manage_ruangan_dan_kategori(): void
    {
        $this->actingAs($this->staf)->get(route('ruangan.create'))->assertForbidden();
        $this->actingAs($this->staf)->post(route('ruangan.store'), ['nama_ruangan' => 'Baru'])->assertForbidden();
        $this->actingAs($this->staf)->post(route('ruangan.move', $this->ruangan), ['direction' => 'up'])->assertForbidden();
        $this->actingAs($this->staf)->delete(route('ruangan.destroy', $this->ruangan))->assertForbidden();

        $this->actingAs($this->staf)->get(route('kategori.create'))->assertForbidden();
        $this->actingAs($this->staf)->post(route('kategori.store'), ['nama_kategori' => 'Baru'])->assertForbidden();
    }

    public function test_admin_can_delete_barang_and_manage_ruangan(): void
    {
        $barang = $this->barangBaru();

        $this->actingAs($this->admin)->delete(route('barang.destroy', $barang))
            ->assertRedirect(route('barang.index'));
        $this->assertDatabaseMissing('barang', ['id' => $barang->id]);

        $this->actingAs($this->admin)->get(route('ruangan.create'))->assertOk();
    }

    public function test_staf_can_export_barang(): void
    {
        $this->barangBaru();

        $this->actingAs($this->staf)->get(route('barang.export', ['format' => 'csv']))->assertOk();
    }

    public function test_staf_can_manage_own_profile(): void
    {
        $this->actingAs($this->staf)->get(route('profile.edit'))->assertOk();

        $this->actingAs($this->staf)->patch(route('profile.update'), [
            'name' => 'Staf Baru',
            'email' => 'staf@sarpras.com',
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', ['id' => $this->staf->id, 'name' => 'Staf Baru']);
    }

    public function test_viewer_can_view_but_cannot_write_anything(): void
    {
        $barang = $this->barangBaru();

        // Boleh melihat semua halaman.
        $this->actingAs($this->viewer)->get(route('dashboard'))->assertOk();
        $this->actingAs($this->viewer)->get(route('barang.index'))->assertOk();
        $this->actingAs($this->viewer)->get(route('ruangan.index'))->assertOk();
        $this->actingAs($this->viewer)->get(route('kategori.index'))->assertOk();

        // Tidak boleh aksi tulis apa pun.
        $this->actingAs($this->viewer)->get(route('barang.create'))->assertForbidden();
        $this->actingAs($this->viewer)->post(route('barang.store'), [])->assertForbidden();
        $this->actingAs($this->viewer)->get(route('barang.edit', $barang))->assertForbidden();
        $this->actingAs($this->viewer)->put(route('barang.update', $barang), [])->assertForbidden();
        $this->actingAs($this->viewer)->delete(route('barang.destroy', $barang))->assertForbidden();
        $this->actingAs($this->viewer)->get(route('barang.import.form'))->assertForbidden();
        $this->actingAs($this->viewer)->get(route('barang.export', ['format' => 'csv']))->assertForbidden();
        $this->actingAs($this->viewer)->get(route('barang.export', ['format' => 'word']))->assertForbidden();
        $this->actingAs($this->viewer)->get(route('ruangan.create'))->assertForbidden();
        $this->actingAs($this->viewer)->post(route('ruangan.store'), ['nama_ruangan' => 'Baru'])->assertForbidden();
        $this->actingAs($this->viewer)->get(route('kategori.create'))->assertForbidden();
        $this->actingAs($this->viewer)->post(route('kategori.store'), ['nama_kategori' => 'Baru'])->assertForbidden();

        // Profil sendiri pun tidak boleh diubah (benar-benar hanya lihat).
        $this->actingAs($this->viewer)->get(route('profile.edit'))->assertOk();
        $this->actingAs($this->viewer)->patch(route('profile.update'), [
            'name' => 'Viewer Nakal',
            'email' => 'guest@sarpras.com',
        ])->assertForbidden();
        $this->actingAs($this->viewer)->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertForbidden();
        $this->actingAs($this->viewer)->delete(route('profile.destroy'))->assertForbidden();

        $this->assertDatabaseHas('barang', ['id' => $barang->id]);
    }
}
