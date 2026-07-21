# Changelog — Backend RentOn

Catatan setiap perubahan pada `RentonBachkEnd-main/`, diurutkan dari yang terbaru. Format: `[YYYY-MM-DD HH:MM]`.

---

## 2026-07-22

### [2026-07-22 05:12] Selaraskan `com.nusatim.sapiriku.api.service` (Android) dengan REST API baru + proteksi secret di git
- **Android — 9 service interface diselaraskan penuh** (`BasicService`, `ChatService`, `NewsService`, `PartnerRewardService`, `RentVehicleService`, `PartnerService`, `CustomerRentService`, `CustomerService`, `PartnerRentService`): verb/path dikoreksi ke kontrak REST baru, body request diganti dari `@FormUrlEncoded` ke `@Body` JSON bertipe, dan seluruh response model direstruktur mengikuti `ApiEnvelope<T>` baru (`api/model/ApiEnvelope.kt`) yang mencerminkan bentuk asli backend `{status, message, data, meta?}` — model lama menaruh field data sejajar dengan `status`/`message`, tidak sesuai kenyataan.
- **Bug ditemukan saat cross-check** (belum diperbaiki): `Customer_m::list_transaction_point()` (api module) tidak ada filter `WHERE account_id` — endpoint `GET api/customer/point_transactions` bocor riwayat poin SEMUA customer, bukan cuma milik akun yang login. Dicatat di [TODO.md](TODO.md).
- **Endpoint yang hilang tanpa pengganti jelas, ditandai di kode alih-alih ditebak:** `Basic::getRecomendationRentVehicle` (fungsinya sekarang menyatu ke `RentVehicle.listVehicle`), `News::listPreview` (model method `News_m::list_preview()` masih ada, tidak ada route yang memanggilnya).
- **Proteksi secret di git:** `.gitignore` ditambah untuk `**/application/config/*/app_secret.php` (nilai secret backend) dan `.claude/` (config tool lokal) — keduanya sempat ter-stage tidak sengaja. `Renton-App-master/configdev.env`/`configprod.env` (berisi secret yang sama di sisi Android, sudah di-gitignore proyek Android itu sendiri) juga di-unstage sebelum commit karena repo ini public.
- **Belum dikerjakan (di luar cakupan sesi ini):** lapisan repository/mapper/ViewModel Android yang memakai 9 service ini akan error kompilasi karena bentuk response berubah total — perlu diperbaiki manual pakai data class baru sebagai acuan. ±45 file model response Android lama sekarang orphan, belum dihapus.

### [2026-07-22 03:46] Fix: `X-App-Secret` masih ditolak walau header sudah dikirim dari app
- **Penyebab:** nilai secret di backend (`application/config/development/app_secret.php`, environment yang aktif untuk `localhost`/LAN IP) beda dengan nilai di `Renton-App-master/configdev.env`/`configprod.env` — bukan bug logika, murni nilai tidak sinkron.
- **Fix:** ketiga environment backend (`development`/`testing`/`production`) diseragamkan ke nilai yang sudah ada di `configdev.env`/`configprod.env`: `f746e5de6f392dc09c120b548e858af95e27e79205843c7e71137afa187a18e5`.

### [2026-07-22 03:41] Fix: transaksi database di seluruh alur finansial (temuan kritis RentOn-Audit-Database-Query.md)
- **Bukti bug nyata sebelum perbaikan** (`admin/models/Customer_m.php::update_topup_status()`): kredit saldo customer dan penandaan `processed=1` adalah 2 statement UPDATE terpisah tanpa transaksi. Kalau proses mati/timeout di antara keduanya, saldo sudah bertambah tapi `processed` masih 0 — panggilan ulang (retry) akan menambah saldo LAGI untuk topup yang sama (double-credit). Pola identik ada di `update_withdraw_status()`.
- **Fix:** membungkus `$this->db->trans_start()`/`trans_complete()` + cek `trans_status()` (return 500 kalau gagal, sebelum notifikasi FCM dikirim) di seluruh endpoint yang melakukan >1 write finansial:
  - `admin/Customer.php`: `topup_status_put()`, `withdraw_status_put()`
  - `api/Customer.php`: `point_exchange_post()` (update saldo+poin & insert riwayat transaksi_point terpisah)
  - `api/RentVehicle.php`: `bookings_post()` (checkout — potong saldo/voucher, insert transaksi, insert timeline)
  - `api/PartnerRent.php`: `booking_done_put()` (settle pembayaran + 2× poin reward + reward partner + komisi agent + kredit saldo — paling kompleks), `bookings_delete()`, `booking_status_put()`, `promotions_delete()` (refund promosi)
  - `api/CustomerRent.php`: `bookings_delete()`, `booking_status_put()`
- **Tidak diubah (sengaja):** `topups_post()`/`withdraws_post()`/`topup_proof_post()` di `api/Customer.php` — masing-masing cuma satu write, tidak butuh transaksi.
- **Catatan sisa:** notifikasi FCM di dalam `_process_partner_rewards()` (dipanggil dari `booking_done_put()`) masih terselip di antara insert reward per-iterasi loop, bukan dipindah ke luar transaksi — kalau butuh kesempurnaan penuh (notifikasi hanya terkirim setelah transaksi pasti sukses), perlu refactor lebih lanjut untuk method itu. Bukan masalah keamanan/uang, cuma potensi notifikasi "nyasar" pada skenario kegagalan yang sangat jarang.

### [2026-07-22 02:35] Fitur: proteksi app-secret khusus modul `api` (mobile app)
- **Kenapa:** sebelum ini, siapa pun yang tahu URL bisa memanggil `api/*` langsung pakai curl/Postman tanpa harus lewat aplikasi Android — tidak ada yang membedakan "request dari app resmi" vs "request dari tool generik". Ini permintaan eksplisit user untuk membatasi akses hanya dari app tertentu.
- **Implementasi:** header baru wajib `X-App-Secret` dicek di `Api_Base_Controller` (baru, `application/libraries/Api_Base_Controller.php`, extends `REST_Base_Controller`) sebelum controller method mana pun jalan — dibandingkan pakai `hash_equals()` (timing-safe) terhadap nilai di `application/config/{development,testing,production}/app_secret.php` (baru, secret 32-byte random per environment). Kesembilan controller modul `api` (`Basic`, `Chat`, `Customer`, `CustomerRent`, `News`, `Partner`, `PartnerRent`, `PartnerReward`, `RentVehicle`) diubah dari `extends REST_Base_Controller` ke `extends Api_Base_Controller`.
- **Sengaja dibatasi hanya modul `api`** — bukan `admin`/`agent`/`auth`, karena hanya `api` yang dipanggil aplikasi Android; ketiga modul lain akan dipanggil frontend admin baru (web), jadi tidak relevan dibatasi dengan "app secret" ala mobile.
- **Batasan yang perlu disadari:** ini bukan proteksi anti-reverse-engineering — secret yang ditanam di APK tetap bisa diekstrak lewat decompile. Fungsinya menyaring tool generik (curl/Postman/scanner otomatis), bukan mencegah penyerang yang serius membongkar APK. Kalau butuh jaminan lebih kuat (verifikasi app+device asli), perlu integrasi Google Play Integrity API — effort jauh lebih besar, ditunda dulu atas keputusan user.
- **`openapi/openapi.json` ikut diperbarui** — 92 operation di 77 path `/api/*` sekarang mendeklarasikan security scheme `AppSecret` (selain `ApiKeyAuth` yang sudah ada), supaya "Try it out" di Swagger tetap bisa dites.
- ⚠️ **Tindakan lanjutan di sisi Android app:** setiap request dari app WAJIB menyertakan header `X-App-Secret: <nilai dari app_secret.php sesuai environment>` — belum diimplementasikan di `Renton-App-master`, perlu ditambahkan (mis. lewat OkHttp interceptor) sebelum app bisa memanggil API lagi.

### [2026-07-22 01:41] Commit: tidak ada perubahan backend baru
- Tidak ada perubahan di `RentonBachkEnd-main/` sejak commit sebelumnya (`a7f2b22`). Commit kali ini hanya menyertakan perubahan `Renton-App-master/` (Android app) yang sedang pending, atas konfirmasi eksplisit.

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
