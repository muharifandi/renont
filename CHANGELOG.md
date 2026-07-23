# Changelog — Backend RentOn

Catatan setiap perubahan pada `RentonBachkEnd-main/`, diurutkan dari yang terbaru. Format: `[YYYY-MM-DD HH:MM]`.

---

## 2026-07-23

### [2026-07-23 18:15] 🚨 Riwayat git ditulis ulang: hapus dump SQL berisi PII pelanggan asli
- **Temuan:** saat membersihkan file/folder tidak penting, ditemukan `database/db_rentone_06_Dec_2021_demo.sql` (ter-track git, ada di 2 path: `RentonBachkEnd-main/database/` dan `RentonBachkEnd-main-backup-2026-07-19/database/`) ternyata berisi 273 alamat email pelanggan **asli** (bukan data dummy walau namanya "demo") — sudah lama tersimpan di repo **public**.
- **Tindakan (atas konfirmasi eksplisit user via pertanyaan pilihan)**: backup mirror-clone penuh dibuat dulu (safety net), lalu `git filter-repo` dipakai untuk menghapus kedua salinan file itu dari **seluruh riwayat commit** (bukan cuma versi terbaru), lalu `git push origin main --force`. Diverifikasi dengan grep konten (bukan cuma nama file) terhadap salah satu email yang bocor di seluruh ~9177 blob objek repo — nihil, benar-benar bersih.
- **`rentone-dengan-data-startup.sql`** (juga ter-track, 6.3MB) sengaja TIDAK ikut dihapus — sudah dicek isinya cuma `admin@admin.com` (seed data biasa, bukan PII).
- **⚠️ Efek samping penting**: seluruh SHA commit berubah karena history ditulis ulang total — semua hash commit yang disebut di changelog ini SEBELUM entry ini (`b9ad776`, `069c759`, `83ea9f8`, dst.) **tidak akan ditemukan lagi** di `git log`, tapi urutan & isi commit-nya tetap sama, hanya identitasnya berubah. Siapa pun yang sudah clone repo ini sebelum operasi ini perlu clone ulang dari nol untuk sinkron.
- **Belum ditindaklanjuti** (di luar cakupan teknis, murni keputusan user): mengecek apakah repo ini sempat di-fork/clone pihak lain sebelum pembersihan (data yang sudah terlanjur diambil orang lain tidak bisa ditarik lewat git history rewrite), dan apakah perlu notifikasi ke pelanggan yang datanya sempat bocor.

### [2026-07-23 17:00] Refactor: pindahkan semua kredensial/secret ke file `.env`
- **Kenapa:** sebelumnya kredensial DB, SMTP, FCM key, `app_secret_key`, dan `encryption_key` tersebar hardcoded literal di 5 file PHP × 3 environment (`development`/`testing`/`production`). Beberapa di antaranya (`database.php`, `mail.php`, `fcm.php`, `app_secret.php`) sudah di-gitignore dari awal proyek supaya tidak ke-push ke repo public, tapi ini bikin setiap environment harus disuntik manual filenya satu-satu, dan `config.php` (tempat `encryption_key`) malah luput dari proteksi ini karena tidak ada di daftar ignore.
- **Implementasi:** `env_loader.php` (baru) — loader `.env` minimal tanpa dependency (proyek ini tidak pakai Composer, jadi tidak bisa `vlucas/phpdotenv`), dipanggil dari `index.php` tepat setelah `ENVIRONMENT` ditentukan dan sebelum CodeIgniter bootstrap. Membaca `.env.{ENVIRONMENT}` dan populate lewat `putenv()`/`$_ENV`/`$_SERVER` (env var asli di server selalu menang kalau sudah ada, tidak akan ditimpa file).
- Kelima file config di ketiga environment (`database.php`, `mail.php`, `fcm.php`, `app_secret.php`, `config.php`) diubah dari nilai literal menjadi `getenv('NAMA_VAR')`.
- **File baru**: `.env.development`, `.env.testing`, `.env.production` (isi nilai asli — gitignored, TIDAK ikut commit ini) dan `.env.example` (template kosong dengan komentar, aman & memang di-commit sebagai dokumentasi).
- **`.gitignore`**: rule lama yang meng-ignore `database.php`/`mail.php`/`fcm.php`/`app_secret.php` dihapus (file-file itu sekarang bebas secret, aman jadi kode biasa yang di-track); diganti `RentonBachkEnd-main/.env.*` dengan pengecualian `!RentonBachkEnd-main/.env.example`.
- **Verifikasi tanpa DB live**: lint (`php -l`) semua file yang disentuh, plus simulasi manual bootstrap (`env_loader.php` dijalankan lalu setiap file config di-`include` dengan `BASEPATH`/`ENVIRONMENT` stub) — seluruh nilai yang ter-resolve (DB host/user/pass/db, SMTP email/password/host/port, FCM key, app secret, encryption key) identik dengan nilai lama sebelum refactor. Tidak ada perubahan perilaku aplikasi.
- **Dampak untuk deploy**: rotasi kredensial (mis. ganti password DB/SMTP production) sekarang cukup edit satu file `.env.production` langsung di server — tidak perlu restart apa pun (file dibaca ulang di setiap request lewat `index.php`) dan tidak perlu ubah kode/commit/push sama sekali. Server yang sudah berjalan (atau server baru) perlu file `.env.{environment}`-nya dibuat manual (dari `.env.example` + nilai asli) karena file ini sengaja tidak ikut ter-push.

### [2026-07-23 15:30] Kerjakan backlog TODO.md sesuai prioritas: fix kebocoran data, cleanup Android, hardening laporan & konfigurasi
- **🔴 Kritis — fix bug kebocoran data**: `api/models/Customer_m.php::list_transaction_point()` menerima `$account_id` sebagai parameter tapi tidak pernah memakainya di query — endpoint `GET api/customer/point_transactions` mengembalikan riwayat poin **seluruh customer**, bukan cuma milik akun yang login. Ditemukan sesi sebelumnya saat penyelarasan Android, baru diperbaiki sekarang: tambah `$this->db->where('account_id',$account_id);` sebelum `order_by`.
- **Verifikasi (bukan perbaikan baru)**: lapisan Android repository/mapper/ViewModel yang sempat dikira akan error kompilasi (dicatat di TODO.md sesi lalu) ternyata **sudah dimigrasi sekaligus** di commit `b9ad776` — dikonfirmasi lewat `./gradlew :app:compileDevelopmentDebugKotlin` → `BUILD SUCCESSFUL`. Header `X-App-Secret` juga diaudit ulang: satu `OkHttpClient`/`Retrofit` di `NetworkModule.kt`, seluruh 9 service Retrofit memakainya, tidak ada client lain yang bypass.
- **Cleanup Android**: hapus 45 file model response lama yang sudah orphan (`api/model/*Response.kt`, peninggalan kontrak API sebelum direstruktur) setelah dipastikan tidak ada referensi eksternal (grep per-class). Sekalian hapus 9 fungsi ekstensi mati di `Mappers.kt` (overload lama yang reseivernya sudah tidak dipakai, kalah oleh overload baru dengan nama sama) dan 1 unused import di `VehicleRepositoryImpl.kt`. Build tetap `BUILD SUCCESSFUL` setelahnya.
- **Hardening laporan PDF admin** (`admin/controllers/Report.php`, `admin/models/Report_m.php`): laporan (`agent_transaction`, `partner_transaction`, `topup`, `withdraw`, `partner_promote_transaction`) sebelumnya cuma mewajibkan `start_date`/`end_date` terisi, tanpa batas lebar rentang atau validasi format — bisa dipakai untuk request rentang bertahun-tahun tanpa `LIMIT` di query (resource exhaustion). Ditambah `_validate_report_date_range()`: validasi format `Y-m-d`, `end_date >= start_date`, maksimal 366 hari — dipanggil di kelima endpoint POST sebelum query jalan.
- **Fix SQL injection minor** di 5 method `*_calculation` pada `Report_m.php`: klausa `WHERE DATE(...) >= '...'` sebelumnya dibangun dengan concat string mentah dari `$param['start_date']`/`end_date` (input POST langsung ke SQL, tanpa escape). Dibungkus `$this->db->escape()` di semua 10 baris (5 method × 2 kolom tanggal).
- **Refactor N+1**: `admin/Config.php::admins_get()` dulu melakukan 1 query semua akun + 1 query terpisah per akun untuk cek keanggotaan grup (N+1). Diganti 1 query `JOIN accounts_groups`+`groups` dengan `GROUP_CONCAT` di `Config_m::get_admins()` (baru) — bentuk response JSON persis sama (field `groups` tetap string nama grup dipisah koma).
- **Isi `encryption_key` CodeIgniter yang kosong** di ketiga environment (`development`/`testing`/`production`) dengan nilai random 64-hex-char berbeda per environment. Dicek dulu lewat grep bahwa `encryption_key` tidak dipakai di mana pun oleh kode aplikasi (hanya TCPDF yang punya properti `encrypted`/`encryptdata` internal sendiri, tidak terkait) — jadi perubahan ini tidak mengubah perilaku apa pun, murni hardening preventif. **Catatan**: ketiga file `config.php` ini sudah ter-track git dan repo public, jadi key ini ikut ke-push — risikonya rendah untuk saat ini karena key belum dipakai apa pun, tapi kalau nanti ada fitur yang benar-benar pakai Encryption library CI, key harus dipindah ke file terpisah yang di-gitignore (pola sama seperti `app_secret.php`) sebelum itu.
- **Hapus `mailtest.php`** dari root `RentonBachkEnd-main/` (file test lama yang mengirim email sungguhan tanpa autentikasi apa pun, temuan audit keamanan sebelumnya) — file ini sudah gitignored dan tidak pernah ter-commit, jadi penghapusan ini hanya di working copy lokal. Kalau file yang sama masih ada di server production/live, perlu dihapus manual di sana juga.
- **Proteksi folder `database/`** dengan `.htaccess` baru (`Require all denied` untuk Apache 2.4 + fallback `Order/Deny` untuk 2.2) — folder ini berisi dump SQL data pelanggan asli.
- **Migration DB baru**: `database/migration_2026_07_23_add_missing_indexes.sql` — 4 index yang sebelumnya cuma rekomendasi di audit (`notification.account_id`, `chat_message.account_id`, `history_partner_reward(account_id,reward_id)`, `partner_rewards(status,feature_id,reward_scope)` — urutan kolom komposit disesuaikan setelah cek ulang ke `Partnerreward_m::list_reward()`, `status` selalu difilter jadi ditaruh di depan). **Sudah dieksekusi user di server** hari ini.
- **Migration lama dikonfirmasi sudah diterapkan**: user mencoba menjalankan ulang `migration_2026_07_21_add_rate_limit_and_key_expiry.sql` dan `migration_2026_07_19_optimize_vehicle_search_index.sql` — keduanya error "Duplicate column/key", yang berarti sudah diterapkan di server sebelumnya (bukan gagal, hanya re-run yang tidak idempotent). Rate-limiting, key-expiry, dan index pencarian kendaraan sudah aktif.
- **Belum dikerjakan (butuh keputusan/akses user, dicatat di TODO.md)**: rotasi kredensial database & SMTP production yang masih hardcoded, aktivasi CORS (menunggu domain frontend admin baru), migrasi FCM ke HTTP v1 (butuh service account baru — push notification saat ini total tidak berfungsi).

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
