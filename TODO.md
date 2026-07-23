# TODO — RentOn

Daftar kerja yang belum selesai, dikumpulkan dari seluruh audit & sesi kerja sampai 22 Juli 2026. Urutan di tiap bagian = prioritas.

---

## 🔴 Prioritas Tinggi

- [x] ~~Fix bug kebocoran data `list_transaction_point()`~~ — diperbaiki 23 Juli 2026, tambah `$this->db->where('account_id',$account_id);` di `Customer_m.php:266`. Lint OK.
- [x] ~~Jalankan migration keamanan~~ (`migration_2026_07_21_add_rate_limit_and_key_expiry.sql`) — dieksekusi user 23 Juli 2026. Error "Duplicate column `date_expires`" saat dijalankan ulang menandakan migration ini **sudah diterapkan sebelumnya** (di sesi/waktu lain) — tabel `limits` + kolom `keys.date_expires` sudah ada, tidak perlu diapa-apakan lagi.
- [x] ~~Jalankan migration index pencarian kendaraan~~ (`migration_2026_07_19_optimize_vehicle_search_index.sql`) — sama seperti di atas, error "Duplicate key `rent_vehicles_item_status`" menandakan seluruh 5 index di `rent_vehicles_item` sudah ada dari sebelumnya. **Belum dikonfirmasi**: index `partners_regencies_id` dan `transaction_rent_vehicle_status_dates` (2 `ALTER TABLE` terpisah setelah yang error) — cek dengan `SHOW INDEX FROM partners;` / `SHOW INDEX FROM transaction_rent_vehicle;` kalau mau pastikan, tapi kemungkinan besar juga sudah ada.
- [x] ~~Jalankan migration index tambahan~~ (`migration_2026_07_23_add_missing_indexes.sql`) — **berhasil dijalankan user 23 Juli 2026**. 4 index baru (`notification`, `chat_message`, `history_partner_reward`, `partner_rewards`) aktif.
- [x] ~~Perbaiki lapisan Android yang memakai 9 service yang baru diselaraskan~~ — sudah dimigrasi di commit `b9ad776` sekaligus, terverifikasi 23 Juli 2026 lewat `./gradlew :app:compileDevelopmentDebugKotlin` → `BUILD SUCCESSFUL`. Catatan lama di TODO ini sudah usang.
- [x] ~~Tambahkan header `X-App-Secret` di semua request Android~~ — diaudit 23 Juli 2026: satu `OkHttpClient`/`Retrofit` tunggal (`NetworkModule.kt`), interceptor terpasang di situ, semua 9 service memakainya. Tidak ada client lain yang bypass.

## 🟠 Prioritas Sedang

- [x] ~~Tambah index database: `history_partner_reward`, `partner_rewards`, `notification`, `chat_message`~~ — querinya sudah dibuat 23 Juli 2026 di `database/migration_2026_07_23_add_missing_indexes.sql` (lihat item migration di 🔴 di atas untuk cara jalankannya). Untuk `partner_rewards`, index dibuat `(status, feature_id, reward_scope)` bukan `(feature_id, reward_scope)` seperti draft awal audit — dicek ulang ke `list_reward()` di `Partnerreward_m.php`, `status` selalu difilter jadi ditaruh di depan komposit sesuai best practice.
- [x] ~~Tambah cap baris/rentang tanggal di `Report_m.php`~~ — selesai 23 Juli 2026. Ditambah `_validate_report_date_range()` di `admin/controllers/Report.php` (validasi format tanggal, `end_date >= start_date`, maks 366 hari), dipanggil di ke-5 endpoint POST report sebelum query jalan. Catatan: query di `Report_m.php` sendiri masih pakai raw string concat untuk `WHERE DATE(...)` tanpa `$this->db->escape()` — bukan bug baru, tapi celah SQLi minor yang belum ikut diperbaiki (ditambahkan ke daftar Rendah di bawah).
- [x] ~~Pindahkan semua kredensial/secret ke file `.env`~~ — selesai 23 Juli 2026. Lihat detail lengkap di bawah (bagian baru).
- [ ] Ganti kredensial database production yang masih hardcoded — **sekarang jauh lebih mudah**: cukup edit `RentonBachkEnd-main/.env.production` (bukan file PHP lagi) — dibaca ulang otomatis di setiap request, tidak perlu restart apa pun. Tetap butuh kredensial baru yang valid dari Anda, saya tidak punya akses untuk membuatkannya.
- [ ] Ganti password SMTP yang masih hardcoded — sama, tinggal edit `MAIL_PASSWORD` di `.env.production`, butuh keputusan/kredensial baru dari Anda.
- [x] ~~Isi `encryption_key` yang masih kosong~~ — selesai 23 Juli 2026 (nilainya sekarang hidup di `.env.{environment}`, lihat bagian .env di bawah).

### 🔒 Migrasi kredensial ke `.env` (baru, 23 Juli 2026)

Semua nilai sensitif (DB, SMTP, FCM key, `app_secret_key`, `encryption_key`) yang tadinya hardcoded literal di 5 file PHP × 3 environment sekarang dibaca dari file `.env.{environment}` lewat `getenv()`:

- **Baru**: `env_loader.php` (loader minimal tanpa dependency, dipanggil dari `index.php` tepat setelah `ENVIRONMENT` ditentukan — sebelum CodeIgniter bootstrap), `.env.development`/`.env.testing`/`.env.production` (isi nilai asli, **gitignore**, tidak pernah masuk repo), `.env.example` (template kosong, aman di-commit, jadi dokumentasi variabel apa saja yang dibutuhkan).
- **Diubah**: `database.php`, `mail.php`, `fcm.php`, `app_secret.php`, `config.php` (baris `encryption_key`) di ketiga environment — semua baca `getenv('NAMA_VAR')` alih-alih literal string.
- **`.gitignore` disesuaikan**: rule lama yang meng-ignore `database.php`/`mail.php`/`fcm.php`/`app_secret.php` dihapus (file-file ini sekarang tidak mengandung secret apa pun, aman di-commit sebagai kode biasa) — diganti dengan ignore `RentonBachkEnd-main/.env.*` (kecuali `.env.example`).
- **Dites**: lint semua file PHP + simulasi bootstrap CI3 (`php -r ...` include manual database.php/mail.php/fcm.php/app_secret.php dengan `env_loader.php` dijalankan lebih dulu) — nilai yang ter-resolve identik dengan nilai lama, jadi tidak ada perubahan perilaku.
- **Untuk deploy ke server baru / server yang sudah ada**: pastikan file `.env.{environment}` yang sesuai ada di root `RentonBachkEnd-main/` di server itu (isi dari `.env.example` + nilai asli) — **file ini tidak ikut ter-push dari sini**, harus disalin/dibuat manual di server (mis. lewat SCP/SFTP atau paste manual), sama seperti dulu kredensial disuntik manual ke file PHP-nya.
- **Konsekuensi bagus**: rotasi kredensial production sekarang cukup edit satu file `.env.production` di server, tidak perlu commit/push kode sama sekali.
- [x] ~~Hapus `mailtest.php` dari web root~~ — dihapus 23 Juli 2026 dari working copy lokal (sudah gitignored, tidak pernah ter-commit). **Catatan**: kalau file ini juga ada di server production/live, itu perlu dihapus manual di sana juga — penghapusan lokal ini tidak otomatis membersihkan deployment yang sudah berjalan.
- [x] ~~Proteksi folder `database/` dengan `.htaccess`~~ — dibuat 23 Juli 2026, `Require all denied` (Apache 2.4) + fallback `Order/Deny` (2.2) supaya kompatibel di kedua versi.
- [x] ~~Hapus ±45 file model response Android yang sudah orphan~~ — selesai 23 Juli 2026. Hapus 45 file (dicek ulang tidak ada referensi eksternal via grep), plus 9 fungsi mati & 1 import tak terpakai di `Mappers.kt`/`VehicleRepositoryImpl.kt`. Build tetap `BUILD SUCCESSFUL` setelahnya.
- [ ] Aktifkan CORS begitu domain frontend admin baru sudah pasti (`check_cors` + `allowed_cors_origins` di `admin`/`agent`/`auth` config).

## 🟡 Prioritas Rendah / Nice-to-have

- [x] ~~SQL injection minor di `Report_m.php` (5 method `*_calculation`, raw string concat tanpa escape)~~ — selesai 23 Juli 2026, dibungkus `$this->db->escape()` di semua 10 baris (5 method × start_date/end_date). Lint OK.
- [ ] `csrf_protection` masih `FALSE` — relevansinya berkurang untuk REST API stateless, tapi sebaiknya tidak dibiarkan tanpa alasan eksplisit.
- [x] ~~Refactor `admin/Config.php::admins_get()` jadi 1 query JOIN~~ — selesai 23 Juli 2026. Dulu: 1 query semua akun + 1 query per akun untuk grup (N+1). Sekarang: 1 query `JOIN accounts_groups/groups` + `GROUP_CONCAT` di `Config_m::get_admins()`, dipanggil dari controller. Response shape sama persis (field `groups` tetap string comma-separated, hanya nama grup staff). Lint OK.
- [ ] Perbaiki anomali skema: `agent_withdraw.status` salah reference ke `customer_withdraw_status`; FK hilang di `partners.agent_id`/`customers.referal_id`/`partners.referal_id`; tabel `config` tanpa `PRIMARY KEY`.
- [ ] Migrasi FCM push notification dari Legacy HTTP API (mati sejak Juni 2024) ke FCM HTTP v1 (OAuth2 service account) — fitur push saat ini sepenuhnya tidak berfungsi apa pun konfigurasinya.
- [ ] Setup Swagger/OpenAPI auto-generate dari anotasi kode (`zircote/swagger-php` + Composer) — saat ini `openapi.json` manual, bisa jadi tidak sinkron kalau ada endpoint baru. **Sudah di-skip user untuk saat ini, jangan dikerjakan tanpa diminta ulang.**
- [ ] Susun ulang dokumen yang hilang dari disk & git history (tidak diketahui penyebabnya, dugaan terkait iCloud Drive): `RentOn-API-Endpoint-Documentation.md`/`-EN.md`, `RentOn-Konversi-REST-API.md`, `RentOn-Audit-Keamanan-Backend.md` — hanya kalau dibutuhkan lagi.

## ✅ Aksi Administratif

- [x] ~~Commit & push perubahan penyelarasan Android service~~ — selesai, commit lama `b9ad776` (**SHA ini sekarang usang, lihat catatan riwayat git di bawah**).
- [x] ~~Hapus file dan folder tidak penting~~ — selesai 23 Juli 2026: `.DS_Store` (11 file, semua platform) dihapus; `Renton-App-master/.gradle/` (64MB, cache Gradle) dan `Renton-App-master/app/build/` (152MB, output build Android) dihapus — keduanya tidak ter-track git, regenerate otomatis di build berikutnya.
- [ ] Pertimbangkan pindahkan folder proyek ini keluar dari `~/Documents` (kemungkinan tersinkron iCloud Drive) — sudah 3 kali file/folder hilang tanpa penjelasan (`.htaccess`, folder backup, beberapa file dokumentasi).

### 🚨 Riwayat git ditulis ulang, 23 Juli 2026 — kebocoran PII

Ditemukan saat mengecek file "tidak penting" di `database/`: **`db_rentone_06_Dec_2021_demo.sql` (ter-track di git, ada di 2 path berbeda: `RentonBachkEnd-main/database/` dan `RentonBachkEnd-main-backup-2026-07-19/database/`) berisi 273 alamat email pelanggan ASLI**, bukan data dummy meski namanya "demo" — di repo **public**.

- **Tindakan (atas persetujuan eksplisit user)**: `git filter-repo` dipakai untuk menghapus kedua salinan file itu dari **seluruh riwayat commit**, lalu `git push origin main --force`. Diverifikasi dengan mencari string email yang bocor (`7fold1999@gmail.com`) di seluruh ~9177 blob objek repo — tidak ditemukan lagi di mana pun.
- **Backup dibuat sebelum operasi**: mirror clone penuh repo (sebelum rewrite) disimpan di scratchpad sesi Claude Code — kalau butuh, beri tahu di sesi berikutnya untuk saya cari lokasinya (sifatnya sementara, bisa hilang kalau sesi lama dibersihkan sistem).
- **⚠️ Dampak yang perlu diketahui**:
  - **Semua SHA commit berubah** karena history ditulis ulang sepenuhnya — semua referensi commit hash di CHANGELOG.md/TODO.md dari sebelum 23 Juli 2026 sore (mis. `b9ad776`, `069c759`, `83ea9f8`) **sudah tidak valid lagi**, tidak akan ditemukan di `git log` sekarang.
  - **Siapa pun yang sudah clone/fork repo ini sebelum operasi ini** akan punya riwayat lama yang divergen — mereka perlu clone ulang dari awal (bukan sekadar `git pull`) untuk sinkron, atau akan mengalami konflik histori yang membingungkan.
  - Force-push membersihkan `origin` di GitHub, tapi **tidak menjamin cache internal GitHub (mis. compare-view lama, PR/issue lama yang mereferensikan commit lama) langsung hilang** — biasanya di-garbage-collect otomatis, tapi kalau butuh kepastian penuh (data ini pernah bocor, bukan cuma risiko teoretis), pertimbangkan hubungi GitHub Support untuk permintaan penghapusan cache eksplisit.
  - `rentone-dengan-data-startup.sql` (juga ter-track, 6.3MB) **TIDAK ikut dihapus** — sudah dicek isinya cuma `admin@admin.com` (seed data biasa, bukan PII), aman dibiarkan.
- **Belum diputuskan**: apakah perlu langkah lanjutan di luar git (mis. cek apakah ada yang sempat fork/clone repo ini, atau notifikasi ke pelanggan yang datanya bocor) — di luar cakupan teknis yang bisa saya lakukan, murni keputusan Anda.

---

*Diperbarui: 23 Juli 2026. Update file ini setiap kali item selesai atau ada temuan baru — jangan biarkan basi.*
