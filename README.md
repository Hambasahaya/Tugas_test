# CI3 Reimbursement Production Package — Documentation

## 1. Arsitektur Solusi

### Diagram Alur Data (textual)

1. Frontend (SPA / Mobile / Web) mengirim HTTP request ke REST API (CodeIgniter 3 Controller).
2. Controller (Auth / Reimbursement) memvalidasi input & session.
3. Controller memanggil Model untuk operasi DB (MySQL).
   - Models: User, Category, Reimbursement, ActivityLog, Email Queue.
4. Model menulis / membaca data dari database.
5. Setelah tindakan penting (submit, approve/reject, delete):
   - Activity log disimpan ke tabel `activity_logs`.
   - Email notifikasi di-_enqueue_ ke tabel `email_queue`.
6. Worker (cron / CLI) memanggil library `Emailqueue->process_pending()` untuk mengirim email secara asinkron.
7. File bukti upload disimpan di `writable/uploads/` (encrypted filename).
8. Admin dapat melihat data soft-deleted (kolom `deleted_at`) melalui endpoint khusus.

### Komponen utama

- `application/controllers/` — API endpoints (Auth, Reimbursement)
- `application/models/` — Akses DB
- `application/libraries/Emailqueue.php` — queue email sederhana
- `application/migrations/` — skema DB & seed
- `writable/uploads/` — penyimpanan file upload

## 2. Penjelasan Desain

### Pendekatan & Alasan

- **CodeIgniter 3.1.13** dipilih untuk kompatibilitas dengan lingkungan legacy dan kemudahan deploy PHP shared hosting.
- **Session-based Auth** (email + password, bcrypt) untuk integrasi sederhana dan keamanan.
- **Hooks** (`RoleHook`) dipakai sebagai middleware karena CI3 tidak memiliki filter middleware native.
- **Email Queue**: CI3 tidak memiliki worker async built-in — queue berbasis tabel memungkinkan pengiriman email secara asinkron melalui cron job.
- **Soft Delete**: Implementasi kolom `deleted_at` memudahkan pemulihan dan audit trail; admin bisa melihat entri yang dihapus.
- **File Upload**: Disimpan di `writable/uploads/` dengan `encrypt_name` untuk menghindari collisions dan predictable filenames.
- **Limit per month (kategori)**: Perhitungan limit dilakukan di model `Reimbursement_model::monthly_total_by_user_category` menggunakan `DATE_FORMAT(submitted_at, '%Y-%m') = 'YYYY-MM'` sehingga validasi dijalankan saat submit (transactional check).

### Integrasi Perhitungan Remunerasi

- Meski sistem ini khusus untuk reimbursement, pola perhitungan limit bulanan dapat diperluas untuk remunerasi/klaim:
  - Setiap kategori memiliki `limit_per_month`.
  - Pada submit, sistem menjumlahkan seluruh pengajuan user di kategori tersebut pada bulan yang sama dan memeriksa apakah jumlah baru melewati batas.
  - Implementasi ini mudah diadaptasi menjadi aturan remunerasi lain (mis. plafon per role, akumulasi tahunan, dsb.)

## 3. Setup & Deploy (Local / Production)

### Prasyarat

- PHP 7.2+ (sesuaikan dengan CI3 requirement)
- MySQL 5.7+ atau MariaDB
- Web server (Apache / Nginx) atau PHP built-in server
- Composer (opsional untuk dev tools)
- Download CodeIgniter 3.1.13 `system/` folder

### Langkah singkat menjalankan secara lokal

1. Letakkan folder `system/` CodeIgniter 3.1.13 di root project (sejajar `index.php`).
2. Salin package ini ke server/dev environment.
3. Ubah `application/config/database.php` sesuai kredensial MySQL Anda.
4. Set `$config['encryption_key']` di `application/config/config.php`.
5. Buat database kosong (mis. `ci_reimbursement_prod`).
6. Jalankan migrasi:
   - Jika tidak menggunakan tool migrasi CI3 CLI, eksekusi SQL dari file `application/migrations/*.php` atau buat small controller untuk menjalankan migrasi.
7. Pastikan `writable/uploads/` dapat ditulisi oleh webserver (`chmod 775` atau sesuai).
8. (Opsional) Buat Cron job untuk memproses email queue:
   ```
   * * * * * php /path/to/index.php cron process_email_queue
   ```
   Anda bisa membuat controller CLI `Cron` yang memanggil `Emailqueue->process_pending()`.

### Menjalankan PHPUnit (tests placeholder)

1. Install phpunit (`composer require --dev phpunit/phpunit`).
2. Sesuaikan `tests/bootstrap.php` untuk load environment & CI system jika ingin tests integration.
3. Jalankan:
   ```
   vendor/bin/phpunit --configuration phpunit.xml
   ```

## 4. Tantangan & Solusi

### Tantangan 1: Tidak ada middleware/filter native (CI3)

**Solusi:** Gunakan `hooks` (post_controller_constructor) untuk pemeriksaan autentikasi/role. Hooks dijalankan cepat dan dapat dikonfigurasi di `application/config/hooks.php`.

### Tantangan 2: Asynchronous email pada aplikasi tanpa worker

**Solusi:** Implementasi `email_queue` table + `Emailqueue` library. Worker dijalankan via cron/CLI untuk memproses antrean. Mudah diintegrasikan dengan supervisor/cron di production.

### Tantangan 3: Validasi limit bulanan di database

**Solusi:** Buat fungsi model yang menghitung `SUM(amount)` per `user_id` + `category_id` pada bulan tertentu menggunakan `DATE_FORMAT(submitted_at, '%Y-%m')`. Validasi dilakukan sebelum insert untuk menghindari pelanggaran aturan.

### Tantangan 4: Keamanan upload dan penyimpanan file

**Solusi:** Batasi MIME/type & ukuran, gunakan `encrypt_name` pada upload, simpan di folder `writable/uploads` dan jangan letakkan file di direktori publik langsung tanpa kontrol.

---

## 4. API Documentation

- Terdapat 2 format dokumentasi di folder `api_docs/`:
  - `Reimbursement_API.postman_collection.json` (Postman v2.1)
  - `swagger.yaml` (OpenAPI 3.0)

Import salah satu di Postman / Swagger UI untuk mencoba endpoints.
