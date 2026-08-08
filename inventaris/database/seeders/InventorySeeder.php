<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ruangan;
use App\Models\KategoriBarang;
use App\Models\Barang;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed STTNI Rooms (Ruangan) grouped by organizational taxonomy
        $roomsByGroup = [
            // Area Akademik & Kelas
            ['nama_ruangan' => 'Ruang Kelas A', 'deskripsi' => 'Ruang kelas teori program Sarjana.'],
            ['nama_ruangan' => 'Ruang Kelas B', 'deskripsi' => 'Ruang kelas teori program Sarjana.'],
            ['nama_ruangan' => 'Ruang Kelas C', 'deskripsi' => 'Ruang kelas teori program Sarjana.'],
            ['nama_ruangan' => 'Ruang Kelas D', 'deskripsi' => 'Ruang kelas teori program Sarjana.'],
            ['nama_ruangan' => 'Ruang Kelas E', 'deskripsi' => 'Ruang kelas teori program Sarjana.'],
            ['nama_ruangan' => 'Laboratorium Komputer', 'deskripsi' => 'Laboratorium komputer praktek lantai 2.'],

            // Area Program Studi & Pasca Sarjana
            ['nama_ruangan' => 'Ruang Kaprodi Teologi', 'deskripsi' => 'Ruang Kaprodi S1 Teologi.'],
            ['nama_ruangan' => 'Ruang Sekretaris Prodi Teologi', 'deskripsi' => 'Ruang sekretariat prodi S1 Teologi.'],
            ['nama_ruangan' => 'Ruang Kaprodi PAK', 'deskripsi' => 'Ruang Kaprodi S1 Pendidikan Agama Kristen.'],
            ['nama_ruangan' => 'Ruang Sekretaris Prodi PAK', 'deskripsi' => 'Ruang sekretariat prodi S1 PAK.'],
            ['nama_ruangan' => 'Ruang Kelas Pasca Sarjana', 'deskripsi' => 'Ruang perkuliahan program Pascasarjana Magister & Doktor.'],
            ['nama_ruangan' => 'Ruang Sekretaris Pasca Sarjana', 'deskripsi' => 'Ruang sekretariat pascasarjana STTNI.'],

            // Area Pimpinan & Rektorat
            ['nama_ruangan' => 'Ruang Ketua', 'deskripsi' => 'Ruang kerja Ketua Sekolah Tinggi.'],
            ['nama_ruangan' => 'Ruang WK I Bidang Akademik', 'deskripsi' => 'Ruang Wakil Ketua I Bidang Akademik.'],
            ['nama_ruangan' => 'Ruang WK II Bidang Keuangan', 'deskripsi' => 'Ruang Wakil Ketua II Bidang Administrasi Umum & Keuangan.'],
            ['nama_ruangan' => 'Ruang Bendahara', 'deskripsi' => 'Ruang transaksi keuangan & administrasi pembayaran mahasiswa.'],
            ['nama_ruangan' => 'Ruang Sekretaris Umum', 'deskripsi' => 'Ruang administrasi rektorat utama.'],

            // Fasilitas Umum & Mahasiswa
            ['nama_ruangan' => 'Gedung Perpustakaan', 'deskripsi' => 'Gedung Perpustakaan STTNI.'],
            ['nama_ruangan' => 'Gedung Kapel', 'deskripsi' => 'Gedung Ibadah dan Pertemuan STTNI.'],
            ['nama_ruangan' => 'Gedung Asrama Putra', 'deskripsi' => 'Gedung tempat tinggal mahasiswa putra.'],
            ['nama_ruangan' => 'Gedung Asrama Putri', 'deskripsi' => 'Gedung tempat tinggal mahasiswi putri.'],
            ['nama_ruangan' => 'Lobby Utama', 'deskripsi' => 'Lobby penerima tamu di gedung utama.'],
            ['nama_ruangan' => 'Ruangan Promosi / BEM', 'deskripsi' => 'Kantor pengurus Badan Eksekutif Mahasiswa (BEM) & PMB.'],
        ];

        $createdRuangans = [];
        foreach ($roomsByGroup as $room) {
            $createdRuangans[$room['nama_ruangan']] = Ruangan::create($room);
        }

        // 2. Seed Categories (Kategori Barang)
        $kategoris = [
            ['nama_kategori' => 'Elektronik'],
            ['nama_kategori' => 'Mebel & Interior'],
            ['nama_kategori' => 'Peralatan Kantor (ATK)'],
            ['nama_kategori' => 'Media Pembelajaran & Sound System'],
            ['nama_kategori' => 'Alat Kebersihan'],
        ];

        $createdKategoris = [];
        foreach ($kategoris as $k) {
            $createdKategoris[$k['nama_kategori']] = KategoriBarang::create($k);
        }

        // 3. Seed Assets (Barang) - one record per unit, each with its own kode
        $barangs = [
            // Ruang Ketua
            [
                'ruangan_id' => $createdRuangans['Ruang Ketua']->id,
                'kategori_id' => $createdKategoris['Mebel & Interior']->id,
                'nama_fasilitas' => 'Meja Kerja Eksekutif Kayu Jati',
                'jumlah' => 1,
                'kondisi' => 'Baik',
                'keterangan' => 'Meja kayu jati ukiran Jepara.',
            ],
            [
                'ruangan_id' => $createdRuangans['Ruang Ketua']->id,
                'kategori_id' => $createdKategoris['Mebel & Interior']->id,
                'nama_fasilitas' => 'Kursi Kerja Direktur Kulit Hitam',
                'jumlah' => 1,
                'kondisi' => 'Baik',
                'keterangan' => 'Kursi hidrolik dengan sandaran tinggi.',
            ],
            [
                'ruangan_id' => $createdRuangans['Ruang Ketua']->id,
                'kategori_id' => $createdKategoris['Elektronik']->id,
                'nama_fasilitas' => 'AC Split Daikin 1 PK',
                'jumlah' => 1,
                'kondisi' => 'Baik',
                'keterangan' => 'Pemeliharaan berkala setiap 3 bulan.',
            ],

            // Laboratorium Komputer
            [
                'ruangan_id' => $createdRuangans['Laboratorium Komputer']->id,
                'kategori_id' => $createdKategoris['Elektronik']->id,
                'nama_fasilitas' => 'PC Client Lenovo ThinkCentre',
                'jumlah' => 25,
                'kondisi' => 'Baik',
                'keterangan' => 'Spesifikasi Core i5, RAM 8GB, SSD 256GB.',
            ],
            [
                'ruangan_id' => $createdRuangans['Laboratorium Komputer']->id,
                'kategori_id' => $createdKategoris['Elektronik']->id,
                'nama_fasilitas' => 'Router Switch Cisco 24 Port',
                'jumlah' => 2,
                'kondisi' => 'Baik',
                'keterangan' => 'Switch utama jaringan Lab.',
            ],
            [
                'ruangan_id' => $createdRuangans['Laboratorium Komputer']->id,
                'kategori_id' => $createdKategoris['Mebel & Interior']->id,
                'nama_fasilitas' => 'Kursi Kerja Ergonomis Staff',
                'jumlah' => 26,
                'kondisi' => 'Kurang Baik',
                'keterangan' => '4 unit kursi hidroliknya macet.',
            ],

            // Ruang Kelas A
            [
                'ruangan_id' => $createdRuangans['Ruang Kelas A']->id,
                'kategori_id' => $createdKategoris['Media Pembelajaran & Sound System']->id,
                'nama_fasilitas' => 'Projector Epson EB-X400',
                'jumlah' => 1,
                'kondisi' => 'Baik',
                'keterangan' => 'Termasuk bracket gantung dan remote.',
            ],
            [
                'ruangan_id' => $createdRuangans['Ruang Kelas A']->id,
                'kategori_id' => $createdKategoris['Mebel & Interior']->id,
                'nama_fasilitas' => 'Kursi Kuliahan Chitose',
                'jumlah' => 40,
                'kondisi' => 'Baik',
                'keterangan' => 'Kursi lipat dengan meja tulis tempel.',
            ],
            [
                'ruangan_id' => $createdRuangans['Ruang Kelas A']->id,
                'kategori_id' => $createdKategoris['Elektronik']->id,
                'nama_fasilitas' => 'Kipas Angin Dinding Cosmos 16"',
                'jumlah' => 2,
                'kondisi' => 'Rusak',
                'keterangan' => '1 unit tidak bisa berputar kiri-kanan.',
            ],

            // Ruang Kaprodi Teologi
            [
                'ruangan_id' => $createdRuangans['Ruang Kaprodi Teologi']->id,
                'kategori_id' => $createdKategoris['Elektronik']->id,
                'nama_fasilitas' => 'Printer Epson L3210 AIO',
                'jumlah' => 1,
                'kondisi' => 'Baik',
                'keterangan' => 'Fasilitas cetak naskah & administrasi prodi.',
            ],
            [
                'ruangan_id' => $createdRuangans['Ruang Kaprodi Teologi']->id,
                'kategori_id' => $createdKategoris['Mebel & Interior']->id,
                'nama_fasilitas' => 'Lemari Dokumen Kaca Lion',
                'jumlah' => 2,
                'kondisi' => 'Baik',
                'keterangan' => 'Lemari arsip prodi pintu geser.',
            ],

            // Gedung Kapel
            [
                'ruangan_id' => $createdRuangans['Gedung Kapel']->id,
                'kategori_id' => $createdKategoris['Media Pembelajaran & Sound System']->id,
                'nama_fasilitas' => 'Sound System Speaker Yamaha DBR15',
                'jumlah' => 4,
                'kondisi' => 'Baik',
                'keterangan' => 'Speaker aktif berkekuatan tinggi.',
            ],
            [
                'ruangan_id' => $createdRuangans['Gedung Kapel']->id,
                'kategori_id' => $createdKategoris['Media Pembelajaran & Sound System']->id,
                'nama_fasilitas' => 'Mixer Audio Behringer 24-Input',
                'jumlah' => 1,
                'kondisi' => 'Baik',
                'keterangan' => 'Terletak di ruang kontrol audio.',
            ],
            [
                'ruangan_id' => $createdRuangans['Gedung Kapel']->id,
                'kategori_id' => $createdKategoris['Mebel & Interior']->id,
                'nama_fasilitas' => 'Mimbar Khotbah Kayu Jati STTNI',
                'jumlah' => 1,
                'kondisi' => 'Baik',
                'keterangan' => 'Mimbar liturgis custom lambang STTNI.',
            ],
            [
                'ruangan_id' => $createdRuangans['Gedung Kapel']->id,
                'kategori_id' => $createdKategoris['Elektronik']->id,
                'nama_fasilitas' => 'AC Floor Standing Polytron 5 PK',
                'jumlah' => 2,
                'kondisi' => 'Mati',
                'keterangan' => '1 unit mati total akibat korsleting sekring.',
            ],

            // Gedung Perpustakaan
            [
                'ruangan_id' => $createdRuangans['Gedung Perpustakaan']->id,
                'kategori_id' => $createdKategoris['Mebel & Interior']->id,
                'nama_fasilitas' => 'Rak Buku Dua Sisi Besi',
                'jumlah' => 12,
                'kondisi' => 'Baik',
                'keterangan' => 'Penyimpanan buku-buku referensi teologi.',
            ],
            [
                'ruangan_id' => $createdRuangans['Gedung Perpustakaan']->id,
                'kategori_id' => $createdKategoris['Elektronik']->id,
                'nama_fasilitas' => 'Barcode Scanner Sirkulasi Buku',
                'jumlah' => 3,
                'kondisi' => 'Baik',
                'keterangan' => 'Scanner USB untuk layanan peminjaman.',
            ],

            // BEM
            [
                'ruangan_id' => $createdRuangans['Ruangan Promosi / BEM']->id,
                'kategori_id' => $createdKategoris['Mebel & Interior']->id,
                'nama_fasilitas' => 'Papan Informasi BEM Whiteboard',
                'jumlah' => 1,
                'kondisi' => 'Baik',
                'keterangan' => 'Papan mading pengumuman mahasiswa.',
            ],
        ];

        foreach ($barangs as $b) {
            $ruangan = Ruangan::findOrFail($b['ruangan_id']);
            $prefix = Barang::kodePrefix($ruangan);
            $seq = Barang::nextSequence($ruangan);

            for ($i = 1; $i <= $b['jumlah']; $i++) {
                Barang::create([
                    'ruangan_id' => $b['ruangan_id'],
                    'kategori_id' => $b['kategori_id'],
                    'nama_fasilitas' => $b['jumlah'] > 1 ? $b['nama_fasilitas'] . ' ' . $i : $b['nama_fasilitas'],
                    'kode_inventaris' => Barang::formatKode($prefix, $seq++),
                    'kondisi' => $b['kondisi'],
                    'keterangan' => $b['keterangan'],
                ]);
            }
        }
    }
}
