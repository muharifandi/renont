# Changelog — Backend RentOn

Catatan setiap perubahan pada `RentonBachkEnd-main/`, diurutkan dari yang terbaru. Format: `[YYYY-MM-DD HH:MM]`.

---

## 2026-07-22

### [2026-07-22 00:48] Fix: request ke seluruh endpoint ditolak "invalid API key" — termasuk login/register
- **Penyebab:** `rest_enable_keys = TRUE` di keempat modul (`api`, `admin`, `agent`, `auth`) membuat library `REST_Controller` mewajibkan header `key` valid untuk SEMUA endpoint sebelum kode controller sempat jalan — termasuk `login`/`register` yang justru seharusnya publik (masalah ayam-telur: butuh key untuk login, padahal login yang memberi key).
- **Fix:** `rest_enable_keys` diubah ke `FALSE` di `application/modules/{api,auth,admin,agent}/config/rest.php`. Otorisasi sesungguhnya tetap berjalan penuh lewat `require_auth()`/`require_auth_group()` di dalam masing-masing method controller (diverifikasi ulang: hanya `index_get()` root—pesan statis tanpa data—dan `__construct()` yang tidak memanggil salah satu dari keduanya).

### [2026-07-22 00:32] Fix: `.htaccess` hilang dari `RentonBachkEnd-main/` → semua URL 404 di Apache
- **Penyebab:** file `.htaccess` (berisi rewrite rule ke `index.php`) tidak ada di disk, hanya tersisa `_htaccess` (nama asli sebelum di-rename, seharusnya sudah di-rename saat instalasi awal). Kemungkinan besar terkait folder proyek ini disinkron iCloud Drive dan dotfile-nya sempat "dioptimasi"/hilang dari disk lokal (lihat juga insiden folder backup di bawah).
- **Fix:** `.htaccess` dibuat ulang dari isi `_htaccess` (isi identik, sesuai yang tercatat di git).

### [2026-07-22 ~00:10] Migration DB untuk fitur keamanan REST API belum dijalankan → error `Table 'renton.limits' doesn't exist`
- **Status:** BELUM diperbaiki otomatis — perlu dijalankan manual oleh user:
  ```bash
  /Applications/XAMPP/xamppfiles/bin/mysql -u root -p renton < RentonBachkEnd-main/database/migration_2026_07_21_add_rate_limit_and_key_expiry.sql
  ```

---

## 2026-07-21 (malam) — Perbaikan Temuan Audit Keamanan REST API

Commit: `10109dd` — lihat detail lengkap di [RentOn-Audit-Keamanan-REST-API.md](RentOn-Audit-Keamanan-REST-API.md).

### [2026-07-21 23:57] Fix: IDOR/BOLA finansial kritis di `PartnerRent.php`
- Cek kepemilikan ditambahkan ke `booking_done_put`, `promotions_delete` (sebelumnya bisa dipakai mencuri saldo refund promosi milik partner lain), `booking_status_put`, `vehicles_put/delete`, `vehicle_photos_delete`, `booking_review_post`, dan `_booking_detail`.
- `PartnerRent.php`/`CustomerRent.php` diubah ke `require_auth_group([GROUP_PARTNER/CUSTOMER])` di seluruh method (sebelumnya `require_auth()` polos — role apa pun bisa memanggil).

### [2026-07-21 23:43] Fix: nol rate-limiting & brute-force protection
- `track_login_attempts = TRUE` diaktifkan di `application/config/{development,testing,production}/ion_auth.php`.
- `rest_enable_limits = TRUE` + `rest_limits_method = 'IP_ADDRESS'` diaktifkan khusus modul `api`+`auth`; anotasi limit 10 percobaan/5 menit per IP ditambahkan ke `Auth::login_post` dan `Customer::login_post`.

### [2026-07-21 23:39] Fix: IDOR lama di `api/Customer.php` (bank & topup) — bug bawaan sebelum konversi REST
- `Customer_m::bank_detail/delete_bank/topup_detail/update_topup` diberi parameter `$account_id` opsional untuk scoping kepemilikan.

### [2026-07-21 ~23:15] Fix: 5 endpoint upload file tanpa batasan tipe (`allowed_types = '*'`)
- Diganti ke whitelist `jpg|jpeg|png` di `admin/News.php`, `admin/Agent.php`, `admin/PartnerReward.php`, `agent/Partner.php`, `agent/Config.php`.
- `data/.htaccess` dibuat (sebelumnya tidak ada) untuk memblokir eksekusi PHP di folder upload sebagai lapisan pertahanan kedua.

### [2026-07-21 23:57] Fix: API key tidak pernah kedaluwarsa
- Kolom `keys.date_expires` (migration baru), key baru otomatis expire 30 hari, diperpanjang tiap login ulang, dicek di `REST_Base_Controller::require_auth()`.

### ⏸ Sengaja ditunda: CORS masih nonaktif di semua environment — menunggu domain frontend admin baru ditentukan.

---

## 2026-07-21 (siang–sore) — Audit & Verifikasi

- **[Audit]** Audit database & query (`RentOn-Audit-Database-Query.md`) — temuan utama: nol transaksi database (`trans_start`/`trans_complete`) di seluruh alur finansial. **Belum diperbaiki.**
- **[Dokumentasi]** Panduan instalasi/konfigurasi (`RentOn-Panduan-Instalasi-Konfigurasi.md`).
- **[Verifikasi]** Kompatibilitas PHP 8.0–8.4 — perbaikan nyata: `#[AllowDynamicProperties]` di 4 file inti CI, parse-error curly-brace dynamic property di `system/libraries/Profiler.php` (3 titik).
- **[Fitur]** Integrasi Swagger/OpenAPI (`openapi/openapi.json` 212 path, `api-docs.html`).
- **[Laporan]** Spesifikasi backend pasca-konversi (`RentOn-Spesifikasi-Backend.md`).
- **[Refactor]** Penghapusan seluruh view lama (49+ file admin, semua view agent/api) pasca konversi ke REST API murni.
- **[Refactor besar]** Konversi seluruh backend (api/admin/agent/auth) dari HTML+session ke REST API stateless berbasis header `key` — arsitektur `REST_Base_Controller`, 212 endpoint.

## 2026-07-19 — Audit & Perbaikan Performa Pencarian Kendaraan

- **[Fix]** Query pencarian kendaraan multi-filter — parameterized `where()`, hapus wrapping `DATE()` non-sargable, perbaiki join foto yang menggandakan baris. Lihat [RentOn-Audit-Performa-Pencarian-Kendaraan.md](RentOn-Audit-Performa-Pencarian-Kendaraan.md).
- **[Migration]** `database/migration_2026_07_19_optimize_vehicle_search_index.sql` — index baru untuk filter pencarian. **Belum dieksekusi ke database.**
- **[Backup]** `RentonBachkEnd-main-backup-2026-07-19/` dibuat sebagai titik rollback sebelum konversi REST. **Catatan: folder ini sudah tidak ada lagi di disk per 2026-07-21** (dugaan sama dengan insiden `.htaccess` — kemungkinan terkait sinkronisasi iCloud Drive pada folder `~/Documents`). Masih ada di histori git (`git show 940d34b`).

---

## ⚠️ Item Diketahui Hilang dari Disk (Belum Dijelaskan Penyebabnya)

Beberapa file yang pernah dibuat selama pengerjaan ini sudah tidak ada di disk maupun git history (artinya sudah hilang sebelum sempat di-commit):
- `RentOn-API-Endpoint-Documentation.md` / `-EN.md`
- `RentOn-Konversi-REST-API.md`
- `RentOn-Audit-Keamanan-Backend.md` (audit keamanan awal, 16 temuan — isinya masih bisa dilihat sebagian dari rujukan di `RentOn-Spesifikasi-Backend.md`)

Kalau dibutuhkan lagi, beri tahu saya — dokumen-dokumen ini bisa disusun ulang.

---

## Belum Diperbaiki (Terdokumentasi, Menunggu Prioritas)

- Nol transaksi database di alur finansial (checkout, booking_done, topup, refund)
- Kredensial database & SMTP hardcoded di repo
- `mailtest.php` masih ada di web root
- `encryption_key` kosong, `csrf_protection` nonaktif
- Folder `database/` tanpa `.htaccess` pelindung
- Index database yang direkomendasikan (`migration_2026_07_19_...sql`) belum dieksekusi
- Migration keamanan REST API (`migration_2026_07_21_...sql`) belum dieksekusi
- Push notification FCM masih pakai Legacy API yang sudah mati sejak Juni 2024
- CORS belum dikonfigurasi untuk admin frontend baru
