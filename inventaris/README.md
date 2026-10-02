# Inventaris STTNI

Aplikasi manajemen inventaris sarana & prasarana STTNI (Laravel 11).
Fitur utama: CRUD Barang / Ruangan / Kategori, Dashboard, ekspor (CSV, Word, JSON),
dan impor CSV dengan deteksi typo nama ruangan.

Panduan ini disusun agar proses instalasi di lokasi tujuan berjalan tanpa error dan
sesuai dengan fitur yang sudah dibangun.

---

## 1. Kebutuhan Perangkat Lunak

| Komponen | Keterangan |
| --- | --- |
| PHP | **8.2+** (disarankan 8.2 - 8.4) |
| Composer | 2.x |
| Node.js & npm | 18+ (hanya untuk build tampilan; opsional bila membawa `public/build`) |
| Web server | Laragon / XAMPP / Apache / Nginx, atau `php artisan serve` untuk uji cepat |
| Database | SQLite (default, paling mudah) atau MySQL / MariaDB |
| Browser | Chrome / Edge / Firefox versi modern |
| Git | opsional |

### Ekstensi PHP yang dibutuhkan

- **Wajib**: `zip` (untuk Export Word), `pdo_sqlite` (DB default) atau `pdo_mysql`,
  `mbstring`, `openssl`, `fileinfo`, `ctype`, `curl`, `tokenizer`, `session`, `dom`, `xml`
- **Tidak dipakai**: `gd`, `intl` (opsional)

> **Penting:** Export Word membutuhkan ekstensi `zip` (`ZipArchive`). Tanpa ekstensi ini,
> tombol Export Word akan gagal. `composer install` juga akan menolak jalan bila `zip`
> tidak tersedia.

---

## 2. Berkas yang Perlu Dibawa

- Seluruh folder project `inventaris`
- `.env.example` — file `.env` **tidak** disertakan; dibuat saat instalasi lalu
  `php artisan key:generate`
- `public/images/logo-sttni.png` (header Word + favicon)
- `public/build/` (hasil kompilasi Vite, agar tidak perlu Node.js)
- `database/database.sqlite` **sudah terisi** (data inventaris + akun admin1/admin2)
- `composer.json` dan `composer.lock` (untuk `composer install`)
- `daftar inventaris sttni - (baru).docx` (sumber perintah `inventory:import-word`)

---

## 3. Konfigurasi `.env`

Sesuaikan nilai berikut:

```dotenv
APP_NAME="Inventaris STTNI"
APP_KEY=            # wajib diisi (lihat langkah instalasi)
APP_ENV=production  # gunakan local saat pengembangan
APP_DEBUG=false     # true hanya saat pengembangan
APP_URL=http://IP-atau-domain
APP_TIMEZONE=Asia/Jakarta   # WIB; sudah menjadi default di .env.example
APP_LOCALE=id               # Bahasa Indonesia; memengaruhi nama bulan di dokumen Word

DB_CONNECTION=sqlite        # atau mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=inventaris
# DB_USERNAME=root
# DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Wajib bila memakai fitur "Lupa Kata Sandi"
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com        # sttni.ac.id memakai Google Workspace
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=alamat@sttni.ac.id
MAIL_PASSWORD=                  # Google App Password (bukan password login)
MAIL_FROM_ADDRESS="inventaris@sttni.ac.id"
MAIL_FROM_NAME="${APP_NAME}"
```

Catatan:
- Login **tidak** butuh email, tetapi fitur **Lupa Kata Sandi** memerlukan SMTP.
  Setelah mengubah `.env`, jalankan `php artisan config:clear`.
- **Google Workspace**: aktifkan 2-Step Verification pada akun pengirim, lalu buat
  **App Password** dan isikan ke `MAIL_PASSWORD`. Password login biasa biasanya ditolak.
- **Jangan ubah `APP_KEY`** — mengubahnya membuat sesi login & data terenkripsi rusak.
- Nama file ekspor otomatis memakai Bahasa Indonesia: `data_inventaris_<Bulan><Tahun>.<ext>`.
- `APP_LOCALE=id` memengaruhi nama bulan **di dalam** isi dokumen Word (mis. "Oktober").

---

## 4. Langkah Instalasi

> **Penting:** paket ini **sudah menyertakan** `database/database.sqlite` yang terisi
> (data inventaris + akun admin1/admin2). **JANGAN** menjalankan
> `php artisan migrate:fresh --seed` atau `php artisan migrate --seed` — akan
> menghapus/menggandakan data yang ada.

```bash
# 1. Install dependency PHP
composer install --optimize-autoloader --no-dev

# 2. Siapkan environment
#    Windows PowerShell:
copy .env.example .env
#    Linux/macOS:
# cp .env.example .env
php artisan key:generate

# 3. Tautkan folder storage
php artisan storage:link

# 4. Verifikasi basis data (cukup dicek, JANGAN di-seed ulang)
php artisan migrate:status

# 5. Bersihkan cache
php artisan optimize:clear

# 6. Jalankan
php artisan serve
# atau arahkan document root web server ke folder "public/"
```

Catatan:
- Aset tampilan **tidak perlu** di-build ulang karena `public/build/` sudah disertakan.
  Jalankan `npm install && npm run build` hanya jika ingin mengubah tampilan.
- Jika memakai SQLite (default), `database/database.sqlite` sudah ada — **jangan**
  membuat/mengosongkannya.
- Hanya bila memakai paket **tanpa** `database.sqlite` (mulai dari nol), barulah
  jalankan `php artisan migrate --seed` untuk membuat tabel + akun admin1/admin2.

---

## 5. Akun Default (hasil seeder)

| Email | Password | Nama & Role |
| --- | --- | --- |
| `admin1@sarpras.com` | `password` | Admin 1 Sarpras |
| `admin2@sarpras.com` | `password` | Admin 2 Sarpras |

> Akun di atas **sudah ada** di `database/database.sqlite` yang disertakan
> (tidak perlu menjalankan seeder).
> **Ganti password** setelah instalasi / serah terima.
> Nama & email bisa diubah sendiri lewat menu **Profil** (langsung tersimpan ke database).

---

## 6. Izin Folder (khusus Linux / hosting)

- `storage/` dan `bootstrap/cache/` harus writable
- `storage/app/` writable — file backup impor ditulis di sini
  (`storage/app/backup_barang_import_*.json`)
- folder `database/` dan `database/database.sqlite` writable (bila memakai SQLite)

---

## 7. Checklist Uji Setelah Instalasi

- [ ] Login dengan kedua akun default
- [ ] CRUD Barang, Ruangan, dan Kategori
- [ ] Export CSV lalu buka di Excel (pemisah `;` + BOM sudah otomatis)
- [ ] Export Word, cek logo STTNI + teks "INVENTARIS STTNI" + tabel tampil benar
- [ ] Unduh template impor, isi, lalu impor (mode **skip** dan **replace**)
- [ ] Coba salah ketik nama ruangan saat impor → muncul saran nama terdekat
- [ ] Pastikan terbentuk file backup di `storage/app/backup_barang_import_*.json`
- [ ] (Opsional) `php artisan inventory:import-word`

---

## 8. Catatan Fitur

- **Export Word wajib ekstensi `zip`.**
- **Impor CSV** — kolom wajib: `nama_fasilitas`, `ruangan`, `kondisi`;
  kolom opsional: `kode_inventaris`, `kategori`, `tahun_pembelian`, `keterangan`.
  Ruangan/kategori **harus sudah ada** dan **tidak** dibuat otomatis; jika tidak cocok
  akan ditolak dengan saran nama terdekat (toleran beda huruf besar/kecil & spasi ganda).
- `kode_inventaris` kosong akan dibuat otomatis (contoh: `INV-RUANGKETUA-001`).
- Sebelum impor, sistem membuat backup otomatis ke `storage/app/`.
- Route pendaftaran (`/register`) masih terbuka; nonaktifkan bila tidak diperlukan.
