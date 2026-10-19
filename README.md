# ExecSync — RESTful API Manajemen Tugas Eksekutif

ExecSync adalah RESTful API berbasis Laravel 11 untuk mengelola delegasi tugas antara Direktur dan Sekretaris. Proyek ini menerapkan autentikasi token menggunakan Laravel Sanctum, pembatasan hak akses berbasis *Role-Based Access Control* (RBAC), serta isolasi kepemilikan data untuk menjaga keamanan akses tugas.

## 🚀 Fitur Utama

- **Autentikasi Token:** Menggunakan Laravel Sanctum untuk autentikasi API dengan *Bearer Token*.
- **Role-Based Access Control (RBAC):**
  - **Direktur:** Dapat membuat tugas baru, mengedit judul dan deskripsi tugas, menghapus tugas, serta memantau tugas yang dibuatnya.
  - **Sekretaris:** Dapat memperbarui status tugas menjadi `pending`, `in_progress`, atau `done`, serta mengunggah file lampiran laporan.
- **Isolasi Data:** Direktur hanya dapat mengakses tugas yang dibuatnya sendiri, sedangkan Sekretaris hanya dapat mengakses tugas yang ditugaskan kepadanya.
- **Manajemen File:** Mendukung unggahan file lampiran menggunakan HTTP method spoofing melalui parameter `_method=PUT` atau `_method=PATCH` pada request `POST` dengan format `multipart/form-data`.
- **Pencarian dan Penyaringan:** Mendukung *query parameters* pada endpoint `GET /api/tasks` untuk mencari tugas berdasarkan judul atau deskripsi serta memfilter tugas berdasarkan status.

## 🛠️ Teknologi yang Digunakan

- **Framework:** Laravel 11
- **Bahasa Pemrograman:** PHP 8.2+
- **Database:** MySQL
- **Autentikasi:** Laravel Sanctum

## 📋 Prasyarat

Pastikan perangkat lunak berikut telah terinstal:

- PHP >= 8.2
- Composer
- MySQL
- Git

## ⚙️ Panduan Instalasi

### 1. Kloning Repositori

```bash
git clone https://github.com/bintangcloud/exec-sync.git
cd exec-sync
```

### 2. Instal Dependensi

```bash
composer install
```

### 3. Konfigurasi Environment

Salin file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

Pada Windows, kamu juga dapat menyalin file `.env.example` secara manual melalui File Explorer.

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Konfigurasi Database

Buat database bernama `exec_sync` di MySQL. Kemudian, sesuaikan konfigurasi database pada file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=exec_sync
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` dengan konfigurasi MySQL lokalmu.

### 6. Jalankan Migrasi dan Seeder

Jalankan perintah berikut untuk membuat tabel sekaligus mengisi data awal melalui seeder:

```bash
php artisan migrate --seed
```

Jika tabel sudah tersedia dan kamu hanya ingin menjalankan seeder:

```bash
php artisan db:seed
```

### 7. Tautkan Storage File

Agar file lampiran tugas dapat diakses melalui URL publik, jalankan:

```bash
php artisan storage:link
```

### 8. Jalankan Server Lokal

```bash
php artisan serve
```

Secara default, aplikasi dapat diakses melalui:

```text
http://127.0.0.1:8000
```

## 🌐 Dokumentasi Endpoint API

Endpoint yang memerlukan autentikasi harus menyertakan header berikut:

| Header | Nilai |
|---|---|
| `Accept` | `application/json` |
| `Authorization` | `Bearer {token_anda}` |

Header `Authorization` tidak diperlukan untuk endpoint autentikasi yang bersifat publik.

### 1. Autentikasi

| Method | Endpoint | Deskripsi | Akses |
|---|---|---|---|
| `POST` | `/api/register` | Mendaftarkan akun baru | Publik |
| `POST` | `/api/login` | Melakukan autentikasi dan memperoleh token | Publik |

### 2. Manajemen Tugas (*Tasks*)

| Method | Endpoint | Deskripsi | Akses |
|---|---|---|---|
| `GET` | `/api/tasks` | Mengambil daftar tugas sesuai hak akses | Direktur / Sekretaris |
| `POST` | `/api/tasks` | Membuat delegasi tugas baru | Direktur |
| `GET` | `/api/tasks/{id}` | Mengambil detail tugas berdasarkan ID | Direktur / Sekretaris |
| `POST` | `/api/tasks/{id}` | Memperbarui tugas menggunakan method spoofing | Sesuai hak akses |
| `DELETE` | `/api/tasks/{id}` | Menghapus tugas beserta file lampirannya | Direktur |

**Catatan:** Endpoint pembaruan menggunakan `POST` dengan parameter `_method=PUT` atau `_method=PATCH` apabila diperlukan untuk unggahan file melalui `multipart/form-data`. Hak akses tetap mengikuti aturan role dan kepemilikan tugas yang diterapkan pada backend.

### 3. Query Parameter: Filter dan Search

Endpoint `GET /api/tasks` mendukung parameter untuk menyaring dan mencari tugas.

| Fitur | Parameter | Contoh |
|---|---|---|
| Filter berdasarkan status | `status` | `/api/tasks?status=pending` |
| Pencarian berdasarkan judul atau deskripsi | `search` | `/api/tasks?search=laporan` |
| Kombinasi filter dan pencarian | `status` dan `search` | `/api/tasks?status=done&search=keuangan` |

**Keterangan:**

- **Filter (`status`):** Menampilkan tugas berdasarkan status, seperti `pending`, `in_progress`, atau `done`.
- **Search (`search`):** Mencari tugas berdasarkan kata kunci pada judul atau deskripsi.
- **Kombinasi:** Kedua parameter dapat digunakan secara bersamaan untuk mempersempit hasil pencarian.

Contoh request:

```http
GET /api/tasks?status=done&search=keuangan
```

Contoh tersebut digunakan untuk mencari tugas berstatus `done` yang judul atau deskripsinya cocok dengan kata kunci `keuangan`.

## 🔑 Akun Pengujian (Seeder)

Setelah menjalankan seeder, akun berikut dapat digunakan untuk pengujian apabila memang telah didefinisikan dalam konfigurasi seeder proyek:

| Role | Email | Password |
|---|---|---|
| Direktur | `direktur@test.com` | `password123` |
| Sekretaris | `sekretaris@test.com` | `password123` |

**Catatan keamanan:** Akun tersebut ditujukan untuk pengujian lokal. Jangan gunakan kredensial bawaan ini pada lingkungan produksi.

## 🔐 Keamanan

ExecSync menerapkan autentikasi berbasis token, pembatasan akses berdasarkan role, dan isolasi data berdasarkan kepemilikan tugas. Setiap pengguna hanya boleh mengakses atau mengubah tugas sesuai dengan hak aksesnya.

## 👨‍💻 Pengembang

**Ni Putu Bintang Permatasari**

Mahasiswa S1 Ilmu Komputer  
Universitas Pendidikan Ganesha (Undiksha)

GitHub: [@bintangcloud](https://github.com/bintangcloud)

---

Dibuat sebagai proyek pengembangan RESTful API menggunakan Laravel 11.
