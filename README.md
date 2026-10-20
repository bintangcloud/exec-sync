# ExecSync — RESTful API Manajemen Tugas Eksekutif

ExecSync adalah RESTful API berbasis Laravel 11 untuk mengelola delegasi tugas antara Direktur dan Sekretaris. Proyek ini menerapkan autentikasi token menggunakan Laravel Sanctum, pembatasan hak akses berbasis **Role-Based Access Control (RBAC)**, serta isolasi kepemilikan data untuk menjaga keamanan akses tugas.

Sistem juga dilengkapi dengan **Admin sebagai pengelola persetujuan akun Direktur**. Pengguna yang mendaftar sebagai Direktur tidak dapat langsung memperoleh akses sebagai Direktur sebelum disetujui oleh Admin. Sementara itu, akun Sekretaris dapat langsung digunakan tanpa memerlukan persetujuan.

## 🚀 Fitur Utama

- **Autentikasi Token:** Menggunakan Laravel Sanctum untuk autentikasi API dengan Bearer Token.
- **Logout:** Mengakhiri sesi autentikasi dengan mencabut token akses yang sedang digunakan.
- **Role-Based Access Control (RBAC):**
  - **Admin:** Menyetujui atau menolak permohonan akun Direktur. Akun Admin dibuat secara manual melalui Laravel Tinker dan tidak dapat diperoleh melalui registrasi publik.
  - **Direktur:** Dapat membuat tugas baru, mengedit judul dan deskripsi tugas, menghapus tugas, serta memantau tugas yang dibuatnya sendiri. Hak akses Direktur hanya aktif setelah akun disetujui Admin.
  - **Sekretaris:** Dapat memperbarui status tugas menjadi `pending`, `in_progress`, atau `done`, serta mengunggah file lampiran laporan. Akun Sekretaris tidak memerlukan persetujuan Admin.
- **Approval Direktur:** Pengguna yang mendaftar sebagai Direktur harus menunggu persetujuan Admin sebelum dapat menggunakan fitur khusus Direktur. Pengguna tidak dapat menyetujui permohonan akunnya sendiri.
- **Isolasi Data:** Direktur hanya dapat mengakses tugas yang dibuatnya sendiri, sedangkan Sekretaris hanya dapat mengakses tugas yang ditugaskan kepadanya.
- **Manajemen File:** Mendukung unggahan file lampiran menggunakan HTTP method spoofing melalui parameter `_method=PUT` atau `_method=PATCH` pada request `POST` dengan format `multipart/form-data`.
- **Pencarian dan Penyaringan:** Mendukung query parameters pada endpoint `GET /api/tasks` untuk mencari tugas berdasarkan judul atau deskripsi serta memfilter tugas berdasarkan status.

## 🛠️ Teknologi yang Digunakan

- **Framework:** Laravel 11
- **Bahasa Pemrograman:** PHP 8.2+
- **Database:** MySQL
- **Autentikasi:** Laravel Sanctum
- **Pengelolaan Hak Akses:** Role-Based Access Control (RBAC)
- **Pengelolaan Akun Admin:** Laravel Tinker

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
| `POST` | `/api/logout` | Logout dan mencabut token akses aktif | Pengguna terautentikasi |

**Ketentuan registrasi:**

- Pengguna dapat mendaftar sebagai Direktur atau Sekretaris sesuai aturan registrasi aplikasi.
- Akun Direktur yang baru didaftarkan berstatus menunggu persetujuan Admin dan tidak boleh menggunakan fitur Direktur sebelum disetujui.
- Akun Sekretaris tidak memerlukan persetujuan Admin.
- Role Admin tidak boleh diberikan melalui registrasi publik.

**Ketentuan logout:**

- Pengguna harus mengirimkan Bearer Token yang valid.
- Sistem mencabut token yang sedang digunakan sehingga token tersebut tidak dapat digunakan kembali untuk mengakses endpoint terproteksi.
- Logout tidak menghapus akun pengguna maupun data tugas yang dimilikinya.

### 2. Manajemen Admin dan Approval Direktur

| Method | Endpoint | Deskripsi | Akses |
|---|---|---|---|
| `GET` | `/api/admin/director-approvals` | Melihat daftar permohonan akun Direktur yang menunggu persetujuan | Admin |
| `PATCH` | `/api/admin/director-approvals/{id}` | Menyetujui atau menolak permohonan akun Direktur | Admin |

Endpoint di atas merupakan rancangan endpoint yang dapat digunakan untuk fitur approval dan perlu disesuaikan dengan implementasi route pada proyek.

**Ketentuan approval:**

- Akun Admin dibuat secara manual melalui Laravel Tinker oleh pengelola aplikasi.
- Admin dapat melihat permohonan akun Direktur yang belum diproses.
- Admin dapat menyetujui atau menolak permohonan akun Direktur.
- Hanya Admin yang berwenang mengubah status persetujuan akun Direktur.
- Direktur tidak dapat menyetujui permohonannya sendiri ataupun mengubah status approval secara langsung.
- Akun Direktur yang belum disetujui harus ditolak ketika mencoba mengakses endpoint khusus Direktur.
- Akun Sekretaris dapat langsung menggunakan fitur sesuai perannya tanpa menunggu approval.

### 3. Manajemen Tugas (*Tasks*)

| Method | Endpoint | Deskripsi | Akses |
|---|---|---|---|
| `GET` | `/api/tasks` | Mengambil daftar tugas sesuai hak akses | Direktur / Sekretaris |
| `POST` | `/api/tasks` | Membuat delegasi tugas baru | Direktur yang disetujui |
| `GET` | `/api/tasks/{id}` | Mengambil detail tugas berdasarkan ID | Direktur / Sekretaris sesuai kepemilikan |
| `POST` | `/api/tasks/{id}` | Memperbarui tugas menggunakan method spoofing | Sesuai hak akses |
| `DELETE` | `/api/tasks/{id}` | Menghapus tugas beserta file lampirannya | Direktur yang disetujui |

**Catatan:** Endpoint pembaruan menggunakan `POST` dengan parameter `_method=PUT` atau `_method=PATCH` apabila diperlukan untuk unggahan file melalui `multipart/form-data`. Hak akses tetap mengikuti aturan role, status approval, dan kepemilikan tugas yang diterapkan pada backend.

### 4. Query Parameter: Filter dan Search

Endpoint `GET /api/tasks` mendukung parameter untuk menyaring dan mencari tugas.

| Fitur | Parameter | Contoh |
|---|---|---|
| Filter berdasarkan status | `status` | `/api/tasks?status=pending` |
| Pencarian berdasarkan judul atau deskripsi | `search` | `/api/tasks?search=laporan` |
| Kombinasi filter dan pencarian | `status` dan `search` | `/api/tasks?status=done&search=keuangan` |

**Keterangan:**

- **Filter (`status`):** Menampilkan tugas berdasarkan status `pending`, `in_progress`, atau `done`.
- **Search (`search`):** Mencari tugas berdasarkan kata kunci pada judul atau deskripsi.
- **Kombinasi:** Kedua parameter dapat digunakan secara bersamaan untuk mempersempit hasil pencarian.

Contoh request:

```http
GET /api/tasks?status=done&search=keuangan
```

Contoh tersebut digunakan untuk mencari tugas berstatus `done` yang judul atau deskripsinya cocok dengan kata kunci `keuangan`.

## 🔑 Membuat Akun Admin melalui Laravel Tinker

Akun Admin tidak dibuat melalui registrasi publik. Untuk membuat akun Admin pertama, gunakan Laravel Tinker dari lingkungan pengembangan atau server yang memiliki akses resmi ke aplikasi.

Jalankan:

```bash
php artisan tinker
```

Contoh pembuatan akun Admin menggunakan model `User`:

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Admin ExecSync',
    'email' => 'admin@execsync.local',
    'password' => Hash::make('GantiDenganPasswordKuat'),
    'role' => 'admin',
]);
```

Sesuaikan nama kolom dengan model dan migrasi proyek. Pastikan kolom `role` tersedia dan dapat diisi melalui konfigurasi model. Jika model menggunakan `$fillable`, tambahkan kolom yang diperlukan atau gunakan mekanisme pengisian atribut yang sesuai.

**Catatan keamanan:**

- Ganti alamat email dan password contoh dengan kredensial yang aman.
- Jangan menyimpan password dalam bentuk teks biasa.
- Batasi akses Tinker hanya untuk pengelola aplikasi yang berwenang.
- Jangan menyediakan endpoint publik yang memungkinkan pengguna mengubah role menjadi `admin` atau menyetujui akun Direktur.
- Pastikan Admin pertama dibuat melalui proses yang aman.

## 🔐 Keamanan

ExecSync menerapkan beberapa lapisan keamanan:

1. **Autentikasi:** Menggunakan Laravel Sanctum untuk memvalidasi token akses.
2. **Logout:** Mencabut token aktif agar tidak dapat digunakan kembali.
3. **RBAC:** Membatasi akses fitur berdasarkan peran Admin, Direktur, dan Sekretaris.
4. **Approval Direktur:** Memastikan akun Direktur baru hanya memperoleh hak akses setelah disetujui Admin.
5. **Registrasi Aman:** Mencegah pengguna memperoleh hak Admin melalui registrasi publik dan mencegah Direktur menyetujui dirinya sendiri.
6. **Isolasi Data:** Membatasi akses tugas berdasarkan kepemilikan dan penugasan.
7. **Validasi Input dan File:** Memvalidasi data permintaan, status tugas, serta tipe dan ukuran file lampiran.

## 👨‍💻 Pengembang

**Ni Putu Bintang Permatasari**

Mahasiswa S1 Ilmu Komputer  
Universitas Pendidikan Ganesha (Undiksha)

GitHub: [@bintangcloud](https://github.com/bintangcloud)

---

Dibuat sebagai proyek pengembangan RESTful API menggunakan Laravel 11.
