# TODO — RentOn

Daftar kerja yang belum selesai, dikumpulkan dari seluruh audit & sesi kerja sampai 22 Juli 2026. Urutan di tiap bagian = prioritas.

---

## 🔴 Prioritas Tinggi

- [ ] **Fix bug kebocoran data**: `Customer_m::list_transaction_point()` (api module) tidak ada filter `WHERE account_id` — endpoint `GET api/customer/point_transactions` mengembalikan riwayat poin **semua customer**, bukan cuma milik akun yang login. Ditemukan saat penyelarasan Android service, belum diperbaiki.
- [ ] **Jalankan migration keamanan** (kalau belum): `RentonBachkEnd-main/database/migration_2026_07_21_add_rate_limit_and_key_expiry.sql` — bikin tabel `limits` + kolom `keys.date_expires`. Tanpa ini rate-limiting & key-expiry belum benar-benar aktif.
- [ ] **Jalankan migration index pencarian kendaraan** (kalau belum): `RentonBachkEnd-main/database/migration_2026_07_19_optimize_vehicle_search_index.sql`.
- [ ] **Perbaiki lapisan Android yang memakai 9 service yang baru diselaraskan** — repository/mapper/ViewModel/UI sekarang akan error kompilasi karena bentuk response berubah total (nested di bawah `data`, field jadi camelCase). Pakai data class baru di `api/model/*.kt` sebagai acuan.
- [ ] **Tambahkan header `X-App-Secret` di semua request Android** (kalau belum lengkap semua) — interceptor contohnya sudah ada, pastikan semua `Service` call lewat client yang sama.

## 🟠 Prioritas Sedang

- [ ] Tambah index database: `history_partner_reward`, `partner_rewards`, `notification`, `chat_message` (lihat [RentOn-Audit-Database-Query.md](RentOn-Audit-Database-Query.md) §Sedang).
- [ ] Tambah cap baris/rentang tanggal di `admin/models/Report_m.php` (laporan PDF tanpa `LIMIT`, resource exhaustion untuk rentang tanggal lebar).
- [ ] Ganti kredensial database production yang masih hardcoded (`application/config/production/database.php`).
- [ ] Ganti password SMTP yang masih hardcoded (`application/config/production/mail.php`).
- [ ] Isi `encryption_key` yang masih kosong (`application/config/production/config.php`).
- [ ] Hapus `mailtest.php` dari web root (`RentonBachkEnd-main/mailtest.php`).
- [ ] Proteksi folder `database/` dengan `.htaccess` (folder ini bisa berisi dump SQL data pelanggan asli).
- [ ] Hapus ±45 file model response Android yang sudah orphan/tidak terpakai di `api/model/` (peninggalan kontrak API lama) — setelah lapisan repository selesai dimigrasi.
- [ ] Aktifkan CORS begitu domain frontend admin baru sudah pasti (`check_cors` + `allowed_cors_origins` di `admin`/`agent`/`auth` config).

## 🟡 Prioritas Rendah / Nice-to-have

- [ ] `csrf_protection` masih `FALSE` — relevansinya berkurang untuk REST API stateless, tapi sebaiknya tidak dibiarkan tanpa alasan eksplisit.
- [ ] Refactor `admin/Config.php::admins_get()` jadi 1 query JOIN (saat ini N+1, dampak kecil).
- [ ] Perbaiki anomali skema: `agent_withdraw.status` salah reference ke `customer_withdraw_status`; FK hilang di `partners.agent_id`/`customers.referal_id`/`partners.referal_id`; tabel `config` tanpa `PRIMARY KEY`.
- [ ] Migrasi FCM push notification dari Legacy HTTP API (mati sejak Juni 2024) ke FCM HTTP v1 (OAuth2 service account) — fitur push saat ini sepenuhnya tidak berfungsi apa pun konfigurasinya.
- [ ] Setup Swagger/OpenAPI auto-generate dari anotasi kode (`zircote/swagger-php` + Composer) — saat ini `openapi.json` manual, bisa jadi tidak sinkron kalau ada endpoint baru. **Sudah di-skip user untuk saat ini, jangan dikerjakan tanpa diminta ulang.**
- [ ] Susun ulang dokumen yang hilang dari disk & git history (tidak diketahui penyebabnya, dugaan terkait iCloud Drive): `RentOn-API-Endpoint-Documentation.md`/`-EN.md`, `RentOn-Konversi-REST-API.md`, `RentOn-Audit-Keamanan-Backend.md` — hanya kalau dibutuhkan lagi.

## ✅ Aksi Administratif

- [ ] Commit & push perubahan penyelarasan Android service (9 file service + model baru) — belum di-commit di sesi ini.
- [ ] Pertimbangkan pindahkan folder proyek ini keluar dari `~/Documents` (kemungkinan tersinkron iCloud Drive) — sudah 3 kali file/folder hilang tanpa penjelasan (`.htaccess`, folder backup, beberapa file dokumentasi).

---

*Diperbarui: 22 Juli 2026. Update file ini setiap kali item selesai atau ada temuan baru — jangan biarkan basi.*
