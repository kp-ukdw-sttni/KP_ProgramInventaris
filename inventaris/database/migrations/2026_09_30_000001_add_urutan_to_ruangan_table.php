<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Urutan resmi tampilan Daftar Ruangan, mengikuti taksonomi STTNI.
     * Ruangan yang tidak ada di daftar ini (mis. hasil import atau tambahan
     * manual) tetap bisa dibuat, hanya ditempatkan setelah daftar ini.
     */
    private const URUTAN_RESMI = [
        'Ruang Kelas A',
        'Ruang Kelas B',
        'Ruang Kelas C',
        'Ruang Kelas D',
        'Ruang Kelas E',
        'Laboratorium Komputer',
        'Ruang Kaprodi Teologi',
        'Ruang Sekretaris Prodi Teologi',
        'Ruang Kaprodi PAK',
        'Ruang Sekretaris Prodi PAK',
        'Ruang Kelas Pasca Sarjana',
        'Ruang Sekretaris Pasca Sarjana',
        'Ruang Ketua',
        'Ruang WK I Bidang Akademik',
        'Ruang WK II Bidang Keuangan',
        'Ruang Bendahara',
        'Ruang Sekretaris Umum',
        'Gedung Perpustakaan',
        'Gedung Kapel',
        'Gedung Asrama Putra',
        'Gedung Asrama Putri',
        'Lobby Utama',
        'Ruangan Promosi / BEM',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ruangan', function (Blueprint $table) {
            $table->unsignedInteger('urutan')->default(0)->after('nama_ruangan')->index();
        });

        $rows = DB::table('ruangan')->orderBy('id')->get();

        $posisiResmi = [];
        $urutanTerakhir = 0;

        foreach ($rows as $row) {
            $posisi = array_search($row->nama_ruangan, self::URUTAN_RESMI, true);

            if ($posisi !== false) {
                $posisiResmi[$row->id] = $posisi + 1;
            }
        }

        if ($posisiResmi !== []) {
            $urutanTerakhir = max($posisiResmi);
        }

        foreach ($rows as $row) {
            $urutan = $posisiResmi[$row->id] ?? ++$urutanTerakhir;

            DB::table('ruangan')->where('id', $row->id)->update(['urutan' => $urutan]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruangan', function (Blueprint $table) {
            $table->dropIndex(['urutan']);
            $table->dropColumn('urutan');
        });
    }
};
