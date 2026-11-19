# TIF Hub

Platform karya mahasiswa Program Studi Teknik Informatika.

Konsep: **Marketplace Proyek + Sistem Tiket** (bukan CRUD kampus biasa).

## Teknologi

- PHP Native 8.4
- MySQL + PDO
- HTML5, CSS3, JavaScript Vanilla
- Arsitektur MVC sederhana (tanpa framework)

## Fitur

### Mahasiswa
- Login / Logout (session + role)
- Beranda marketplace (proyek terbaru, populer, kategori, semester)
- Katalog proyek dengan search, filter, sorting, pagination
- Detail proyek + link GitHub & demo
- Sistem tiket (buat, lihat, balas, timeline)
- Profil + daftar proyek kelompok

### Admin
- Dashboard statistik
- CRUD Mahasiswa
- CRUD Proyek (publish/unpublish, anggota kelompok)
- **Upload foto proyek** (multi foto, drag & drop, preview, pilih cover, hapus foto, **atur urutan foto**)
- Manajemen Tiket (ubah status, balas, catatan internal)

## Instalasi

### 1. Import database

```bash
mysql -u root -p < database/migrations.sql
```

Atau lewat HeidiSQL / phpMyAdmin di Laragon.

> **Sudah punya database `tif_hub` dari versi sebelumnya?** Jangan import `migrations.sql` (akan menghapus data).
> Cukup jalankan: `mysql -u root -p < database/add_project_images.sql` untuk menambah tabel foto.

### 2. Konfigurasi `.env`

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=tif_hub
DB_USER=root
DB_PASS=
```

### 3. Jalankan server

Cara termudah (sudah memakai batas upload 10 MB/foto dan 40 MB/total):

```bash
# Windows: klik dua kali / jalankan
serve.bat

# macOS / Linux
./serve.sh
```

Atau manual:

```bash
php -d upload_max_filesize=10M -d post_max_size=40M -S localhost:8001 -t public
```

> Tanpa flag `-d` di atas, PHP memakai batas bawaan (2 MB/file, 8 MB/total) sehingga foto besar bisa ditolak.

Buka: **http://localhost:8001**

### Laragon / Apache / hosting

- Arahkan document root ke folder `public/`, **atau** biarkan di root project: file `.htaccess` di root meneruskan semua request ke `public/`.
- `public/.htaccess` mengarahkan URL cantik (`/admin/projects`) ke `index.php`.
- Di hosting seperti InfinityFree, upload isi project ke `htdocs/` apa adanya. Pastikan `public/uploads/projects/` dapat ditulis (chmod 755/775).

### Jika muncul halaman "Ups, terjadi kesalahan"

Set `APP_DEBUG=true` di `.env` untuk melihat detail error. Penyebab paling umum: tabel `project_images` belum ada (jalankan `database/add_project_images.sql`) atau MySQL belum menyala.

## Akun Demo

| Role       | Email                  | Password     |
|------------|------------------------|--------------|
| Admin      | admin@tifhub.local     | password123  |
| Mahasiswa  | mahasiswa@tifhub.local | password123  |

## Struktur Folder

```
tif-project-hub/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   ├── Core/
│   └── Helpers/
├── config/
├── public/          ← document root
├── routes/
├── database/
├── storage/
├── .env
└── README.md
```

## Upload Foto Proyek

- Admin → **Proyek** → Tambah/Edit → bagian **Foto Proyek**.
- Format: JPG, PNG, WEBP, GIF · maks 5 MB/foto · hingga 10 foto sekali simpan.
- Foto dikecilkan otomatis di browser (maks 1600px) agar upload cepat. JPG tetap JPG, PNG/WEBP menjadi WEBP (transparansi aman), GIF tidak diubah.
- Batas per foto dan total upload dibaca dari server; foto yang melewati batas ditolak dengan pesan jelas sebelum dikirim.
- Urutan foto: seret & lepas (desktop) atau tombol ‹ › (HP). Foto cover selalu tampil pertama di galeri.
- Foto pertama otomatis jadi **cover** (tampil di katalog); bisa diganti di halaman Edit.
- File disimpan di `public/uploads/projects/` dengan nama acak. Validasi MIME asli, bukan hanya ekstensi.
- Jika upload gagal karena batas server, jalankan lewat `serve.bat` / `serve.sh`, atau naikkan `upload_max_filesize` dan `post_max_size` di `php.ini` (mis. 10M dan 40M).

## Catatan

- Source code proyek **tidak** disimpan di server, hanya metadata + URL GitHub.
- Password di-hash dengan `password_hash()` / `password_verify()`.
- CSRF protection aktif pada semua form POST.
- Role guard: mahasiswa ↔ admin terpisah.
