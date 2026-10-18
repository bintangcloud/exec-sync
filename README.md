# ExecSync — RESTful API Manajemen Tugas Eksekutif

ExecSync adalah RESTful API berbasis Laravel 11 untuk mengelola delegasi tugas antara Direktur dan Sekretaris. Proyek ini menerapkan autentikasi token menggunakan Laravel Sanctum, pembatasan hak akses berbasis *Role-Based Access Control* (RBAC), serta isolasi kepemilikan data untuk menjaga keamanan akses tugas.

## 🚀 Fitur Utama

- **Autentikasi Token:** Menggunakan Laravel Sanctum untuk autentikasi API dengan *Bearer Token*.
- **Role-Based Access Control (RBAC):**
  - **Direktur:** Dapat membuat tugas baru, mengedit judul dan deskripsi tugas, menghapus tugas, serta memantau tugas yang dibuatnya.
  - **Sekretaris:** Dapat memperbarui status tugas menjadi `pending`, `in_progress`, atau `done`, serta mengunggah file lampiran laporan.
- **Isolasi Data:** Direktur hanya dapat mengakses tugas yang dibuatnya sendiri, sedangkan Sekretaris hanya dapat mengakses tugas yang ditugaskan kepadanya.
- **Manajemen File:** Mendukung unggahan file lampiran melalui *HTTP method spoofing* menggunakan parameter `_method=PUT` atau `_method=PATCH` pada request `POST` dengan format `multipart/form-data`.

## 🛠️ Teknologi yang Digunakan

- **Framework:** Laravel 11
- **Bahasa Pemrograman:** PHP 8.2+
- **Database:** MySQL
- **Autentikasi:** Laravel Sanctum
- **Pengujian API:** Postman

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

Salin file konfigurasi environment:

```bash
cp .env.example .env
```

> **Catatan untuk Windows:** Kamu juga bisa menyalin `.env.example` menjadi `.env` secara manual melalui File Explorer.

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Konfigurasi Database

Buka file `.env`, kemudian sesuaikan konfigurasi database MySQL dengan pengaturan lokalmu:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=exec_sync
DB_USERNAME=root
DB_PASSWORD=
```

Pastikan database `exec_sync` sudah dibuat di MySQL sebelum melanjutkan.

### 6. Jalankan Migrasi Database

```bash
php artisan migrate
```

### 7. Jalankan Server Lokal

```bash
php artisan serve
```

Secara default, API dapat diakses melalui:

```text
http://127.0.0.1:8000
```

## 🧪 Pengujian API

Pengujian endpoint dapat dilakukan menggunakan Postman dengan langkah berikut:

1. Jalankan server Laravel.
2. Lakukan autentikasi melalui endpoint login yang tersedia.
3. Gunakan token yang diperoleh sebagai *Bearer Token* pada request yang memerlukan autentikasi.
4. Uji endpoint sesuai dengan role Direktur atau Sekretaris.
5. Pastikan setiap pengguna hanya dapat mengakses tugas sesuai dengan hak akses dan kepemilikannya.

## 🔐 Keamanan

ExecSync menerapkan autentikasi berbasis token, pembatasan akses berdasarkan role, dan isolasi data berdasarkan kepemilikan tugas. Pengguna yang tidak memiliki izin tidak seharusnya dapat mengakses atau mengubah tugas milik pengguna lain.

## 👩‍💻 Pengembang

**Bintang Permatasari**

GitHub: [@bintangcloud](https://github.com/bintangcloud)

---

Dibuat sebagai proyek pengembangan RESTful API menggunakan Laravel.
