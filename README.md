```markdown
# ExecSync - RESTful API Manajemen Tugas Eksekutif

API RESTful yang dibangun menggunakan Laravel 11 untuk mengelola delegasi tugas antara Direktur dan Sekretaris. Proyek ini mengimplementasikan autentikasi token menggunakan Laravel Sanctum, pembatasan hak akses berbasis *role* (RBAC), serta isolasi kepemilikan data.

## 🚀 Fitur Utama

- **Autentikasi Token:** Menggunakan Laravel Sanctum untuk manajemen *Bearer Token*.
- **Role-Based Access Control (RBAC):** 
  - **Direktur:** Dapat membuat tugas baru, mengedit judul/deskripsi tugas, menghapus tugas, dan memantau tugas yang dibuatnya.
  - **Sekretaris:** Dapat mengubah status tugas (`pending`, `in_progress`, `done`) dan mengunggah file lampiran laporan.
- **Isolasi Data Aman:** Direktur hanya dapat melihat tugas yang ia buat. Sekretaris hanya dapat melihat tugas yang ditugaskan khusus untuknya.
- **Manajemen File (Method Spoofing):** Menangani *upload* file secara RESTful menggunakan trik HTTP POST dengan `_method=PUT/PATCH` karena batasan pembacaan *form-data* bawaan PHP.

## 🛠️ Teknologi yang Digunakan

- **Framework:** Laravel 11 (PHP 8.x)
- **Database:** MySQL
- **Autentikasi:** Laravel Sanctum
- **Pengujian API:** Postman

## 📋 Prasyarat

Pastikan perangkat lunak berikut sudah terinstal di komputermu:
- PHP >= 8.2
- Composer
- MySQL
- Git

## ⚙️ Panduan Instalasi

1. **Kloning Repositori**
   ```bash
   git clone [https://github.com/bintangcloud/exec-sync.git](https://github.com/usebintangcloud/exec-sync.git)
   cd ExecSync

```

2. **Instal Dependensi**
```bash
composer install

```


3. **Konfigurasi Environment**
Salin file `.env.example` menjadi `.env` lalu sesuaikan kredensial database kamu:
```bash
cp .env.example .env

```


4. **Generate Application Key**
```bash
php artisan key:generate

```


5. **Migrasi Database**
Jalankan perintah ini untuk membuat struktur tabel:
```bash
php artisan migrate

```


6. **Tautkan Storage File**
Agar file *attachment* tugas bisa diakses, buat tautan (*symlink*) ke folder *public*:
```bash
php artisan storage:link

```


7. **Jalankan Server**
```bash
php artisan serve

```


API sekarang dapat diakses melalui `http://127.0.0.1:8000`.

## 🌐 Dokumentasi Endpoint API

Semua *endpoint* di bawah ini wajib menyertakan **Header**:

* `Accept: application/json`
* `Authorization: Bearer {token_anda}` *(kecuali endpoint autentikasi)*

### 1. Autentikasi

| Method | Endpoint | Deskripsi | Akses |
| --- | --- | --- | --- |
| `POST` | `/api/register` | Mendaftarkan akun baru (`role`: `direktur` / `sekretaris`) | Publik |
| `POST` | `/api/login` | Mendapatkan Bearer Token | Publik |

### 2. Manajemen Tugas (Tasks)

| Method | Endpoint | Deskripsi | Akses |
| --- | --- | --- | --- |
| `GET` | `/api/tasks` | Mengambil daftar tugas (terisolasi sesuai hak akses) | Direktur / Sekretaris |
| `POST` | `/api/tasks` | Membuat delegasi tugas baru | Hanya Direktur |
| `GET` | `/api/tasks/{id}` | Mengambil detail 1 tugas spesifik | Direktur / Sekretaris |
| `POST` | `/api/tasks/{id}` | *Update* tugas (wajib pakai form-data `_method=PUT`) | Direktur / Sekretaris |
| `DELETE` | `/api/tasks/{id}` | Menghapus tugas beserta file lampirannya | Hanya Direktur |

