# Panduan Instalasi, Akses & Konfigurasi — Backend RentOn

**Untuk:** `RentonBachkEnd-main/` (REST API, CodeIgniter 3.1.10, kompatibel PHP 8.0–8.4)

---

## 1. Persyaratan Sistem

| Komponen | Versi | Catatan |
|---|---|---|
| PHP | **8.0 – 8.4** (direkomendasikan) atau 7.4 | Sudah diverifikasi & diperbaiki agar kompatibel — lihat [RentOn-Spesifikasi-Backend.md](RentOn-Spesifikasi-Backend.md) §2.1 |
| Ekstensi PHP wajib | `mysqli`, `gd` atau `gd2` (untuk resize foto & TCPDF), `curl` (untuk FCM), `mbstring`, `openssl`, `zip`, `intl` | `gd`/`curl` kalau tidak aktif akan bikin upload foto & notifikasi push gagal diam-diam |
| Database | MySQL 5.7+ / MariaDB 10.2+ | 69 tabel, lihat [RentOn-DDL-UML-Diagram-Flowchart-Backend.md](RentOn-DDL-UML-Diagram-Flowchart-Backend.md) |
| Web server | Apache dengan `mod_rewrite` aktif | Proyek ini bergantung pada `.htaccess` (`RewriteEngine on`) — kalau pakai Nginx, aturan rewrite perlu ditranslasi manual |
| Composer | ❌ Tidak dipakai | Semua dependency (Ion Auth, TCPDF, MX/HMVC) sudah dibundel langsung di `application/third_party/` dan `application/libraries/` |

---

## 2. Langkah Instalasi

### 2.1 Salin kode ke web root
```bash
# taruh isi RentonBachkEnd-main/ sebagai document root virtual host, misal:
/var/www/renton/  ← isi persis RentonBachkEnd-main/
```

### 2.2 Buat database & import
```bash
mysql -u root -p -e "CREATE DATABASE rentone CHARACTER SET utf8 COLLATE utf8_general_ci;"
mysql -u root -p rentone < database/db_rentone_06_Dec_2021.sql
# lalu jalankan migration index performa (WAJIB, belum pernah dieksekusi):
mysql -u root -p rentone < database/migration_2026_07_19_optimize_vehicle_search_index.sql
```
> Ada 3 pilihan dump di `database/`: `db_rentone_06_Dec_2021.sql` (data produksi lama — **hati-hati berisi data pelanggan asli**), `..._demo.sql` (dataset demo), `rentone-dengan-data-startup.sql` (seed data awal kosong). Pilih sesuai kebutuhan (instalasi baru → pakai yang startup/demo).

### 2.3 Set permission folder upload
```bash
chmod -R 755 data/
# pastikan folder ini writable oleh user web server (www-data/apache):
# data/customers/{profile,files,topup}, data/partners/{profile,files}, data/agents/{profile,files}, data/vehicles, data/news, data/rewards
chown -R www-data:www-data data/
```

### 2.4 Tentukan environment
Aplikasi ini menentukan environment **otomatis dari header `Host`** (lihat `index.php`):
```php
switch ($domain) {
  case 'renton.co.id':     ENVIRONMENT = 'production'; break;
  case 'cp.renton.co.id':  ENVIRONMENT = 'testing'; break;
  default:                 ENVIRONMENT = 'development'; break;
}
```
⚠️ **Kalau domain Anda BUKAN `renton.co.id`, edit `index.php` baris ~62** dan ganti ke domain Anda sendiri — kalau tidak, aplikasi akan selalu jalan di mode `development` (menampilkan error PHP mentah ke publik, resiko keamanan — lihat temuan T5 di [RentOn-Audit-Keamanan-Backend.md](RentOn-Audit-Keamanan-Backend.md)).

### 2.5 Cek instalasi berjalan
Buka `https://domain-anda/` → default controller `admin/dashboard` akan merespons JSON singkat (`{"status":true,"message":"Admin Dashboard API — RentOn",...}`). Kalau muncul error PHP, cek koneksi database dulu (langkah 3 di bawah).

---

## 3. Konfigurasi

Semua config CodeIgniter ada di `application/config/{development|testing|production}/` — **environment ditentukan otomatis** sesuai §2.4, jadi edit folder yang sesuai domain target Anda.

### 3.1 Database — `application/config/{env}/database.php`
```php
$db['default'] = array(
    'hostname' => 'localhost',
    'username' => 'u6272621_rentone',   // ⚠️ GANTI — kredensial lama masih di repo
    'password' => 'rentone123',          // ⚠️ GANTI — password lemah, anggap sudah bocor
    'database' => 'u6272621_rentone',    // ⚠️ GANTI sesuai nama DB Anda
    'dbdriver' => 'mysqli',
);
```

### 3.2 Base URL & keamanan dasar — `application/config/{env}/config.php`
| Setting | Nilai sekarang | Wajib diganti? |
|---|---|---|
| `base_url` | (biasanya auto-detect atau kosong) | Isi manual kalau auto-detect gagal, mis. `'https://renton.co.id/'` |
| `encryption_key` | `''` (kosong) | ✅ **Wajib diisi** — `bin2hex(random_bytes(32))` |
| `csrf_protection` | `FALSE` | Untuk REST API tidak wajib (auth pakai header `key`, bukan cookie), tapi disarankan tetap diaktifkan sebagai defense-in-depth |
| `cookie_secure` / `cookie_httponly` | `FALSE` / `FALSE` | ✅ Set `TRUE` kalau situs sudah HTTPS |

### 3.3 Email/SMTP (untuk reset password) — `application/config/{env}/mail.php`
```php
$config['mail_email']    = 'verifikasi@renton.co.id';
$config['mail_password'] = 'rentone1234';   // ⚠️ GANTI — password SMTP asli masih di repo
$config['mail_setting']  = ['smtp_host' => 'ssl://mail.renton.co.id', 'smtp_port' => 465, ...];
```
Dipakai oleh `POST auth/forgot_password` (lihat §4). Kalau pakai Gmail SMTP, aktifkan "less secure apps" atau (lebih baik) pakai App Password.

### 3.4 Firebase Cloud Messaging — `application/config/{env}/fcm.php`
```php
$config['fcm_api_key_android'] = 'AAAAw4bBQ_M:...';  // Legacy FCM Server Key
$config['fcm_api_send_address'] = 'https://fcm.googleapis.com/fcm/send';
```
🔴 **PERINGATAN PENTING:** ini memakai **Legacy FCM HTTP API**, yang **sudah resmi dimatikan Google sejak Juni 2024**. Endpoint `fcm.googleapis.com/fcm/send` tidak lagi merespons — **semua push notification di aplikasi ini sudah tidak berfungsi** apa pun API key-nya, sampai `application/libraries/Fcm.php` ditulis ulang untuk memakai **FCM HTTP v1 API** (butuh OAuth2 service-account JSON, bukan lagi server key sederhana). Ini pekerjaan terpisah yang belum dikerjakan — beri tahu saya kalau mau diperbaiki.

### 3.5 Ion Auth (branding email & role) — `application/config/{env}/ion_auth.php`
```php
$config['site_title']  = "Orderki";              // ⚠️ Sisa boilerplate lama, ganti ke "RentOn"
$config['admin_email'] = "no-replay@orderki.com"; // ⚠️ Ganti ke email asli RentOn
```
File ini sisa dari template starter "Orderki" yang jadi basis proyek — belum di-rebrand sepenuhnya.

### 3.6 Ringkasan checklist sebelum live
- [ ] Ganti kredensial database (§3.1)
- [ ] Isi `encryption_key` (§3.2)
- [ ] Ganti password SMTP (§3.3)
- [ ] Perbaiki/nonaktifkan FCM sampai dimigrasi ke v1 API (§3.4)
- [ ] Ganti branding Ion Auth dari "Orderki" (§3.5)
- [ ] Hapus `mailtest.php`, proteksi folder `database/` dengan `.htaccess` — lihat [RentOn-Audit-Keamanan-Backend.md](RentOn-Audit-Keamanan-Backend.md)

---

## 4. Cara Akses

### 4.1 Dokumentasi interaktif (Swagger)
Buka **`https://domain-anda/api-docs.html`** — tampil Swagger UI dengan semua 212 endpoint, bisa langsung "Try it out" dari browser.

### 4.2 Autentikasi — satu mekanisme untuk semua role
Semua endpoint (kecuali yang publik) butuh header HTTP:
```
key: <api_key_anda>
```

**Cara dapat `key` tergantung role:**

| Role | Cara dapat key |
|---|---|
| Customer | `POST api/customer` (register) atau `POST api/customer/login` |
| Partner | `POST api/partner` (setelah login sebagai customer) |
| Admin/Supervisor/Staff/Reader/Agent | `POST auth/login` — body `{"email":"...", "password":"..."}` |

Contoh login staff/agent:
```bash
curl -X POST https://domain-anda/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@renton.co.id","password":"..."}'
# → { "status": true, "data": { "key": "8f2a1c9d...", "groups": [1], ... } }
```

Contoh pemakaian key:
```bash
curl https://domain-anda/admin/dashboard/summary?start=2026-07-01&end=2026-07-21 \
  -H "key: 8f2a1c9d..."
```

### 4.2b Header wajib tambahan khusus `api/*` (mobile app) — `X-App-Secret`
Sejak 22 Juli 2026, **seluruh endpoint di modul `api`** (yang dipanggil aplikasi Android — bukan `admin`/`agent`/`auth`) mewajibkan satu header tambahan di luar `key`:
```
X-App-Secret: <nilai dari application/config/{env}/app_secret.php>
```
Tanpa header ini, semua request ke `api/*` akan ditolak `403 Forbidden` — **termasuk endpoint publik** seperti `login`/`register`/browsing kendaraan, karena pengecekan ini terjadi sebelum controller method jalan sama sekali.

- Nilai secretnya ada di `application/config/{development,testing,production}/app_secret.php` (beda per environment, di-generate acak 32-byte saat pembuatan — **ganti dengan nilai Anda sendiri sebelum deploy production**, jangan pakai nilai bawaan dari repo).
- Ini proteksi level **aplikasi**, terpisah dari `key` (yang levelnya per-akun). Tujuannya menyaring tool generik (curl/Postman/scanner otomatis) yang mencoba memanggil API langsung tanpa lewat app.
- ⚠️ **Bukan proteksi anti-reverse-engineering** — secret yang ditanam di APK tetap bisa diekstrak lewat decompile. Kalau butuh verifikasi app+device asli yang sesungguhnya, perlu Google Play Integrity API (belum diimplementasikan).
- **Aplikasi Android wajib mengirim header ini di setiap request** ke `api/*` — kalau belum ditambahkan di sisi app, semua panggilan API dari app akan gagal 403 sampai ditambahkan.

Contoh:
```bash
curl -X POST https://domain-anda/api/customer/login \
  -H "Content-Type: application/json" \
  -H "X-App-Secret: <secret_environment_anda>" \
  -d '{"email":"...","password":"..."}'
```

### 4.3 Struktur URL
```
{domain}/api/{controller}/{aksi}       ← mobile app (customer & partner)
{domain}/admin/{controller}/{aksi}     ← backoffice (butuh key role admin/supervisor/staff/reader)
{domain}/agent/{controller}/{aksi}     ← portal agent (butuh key role agent)
{domain}/auth/{login|logout|...}       ← autentikasi staff & agent
```
Peta lengkap 212 endpoint: [RentOn-Konversi-REST-API.md](RentOn-Konversi-REST-API.md) §3, atau langsung via Swagger (§4.1).

### 4.4 Membuat akun admin pertama kali
Belum ada endpoint "seed admin pertama" — cara tercepat: insert manual ke tabel `accounts` + `accounts_groups` (group_id `1` = admin), dengan password di-hash pakai `password_hash($pw, PASSWORD_DEFAULT)` di PHP, atau jalankan potongan kode PHP kecil yang memanggil `$this->ion_auth->register(...)` lalu `add_to_group(1, $id)`.

---

## 5. Yang Perlu Diketahui Sebelum Deploy Sungguhan

Panduan ini fokus ke instalasi & konfigurasi. Untuk status kesiapan produksi secara menyeluruh (keamanan, performa, pengujian yang belum dilakukan), rujuk:
- [RentOn-Spesifikasi-Backend.md](RentOn-Spesifikasi-Backend.md) — ringkasan status & gap
- [RentOn-Audit-Keamanan-Backend.md](RentOn-Audit-Keamanan-Backend.md) — 16 temuan keamanan (beberapa masih terbuka)
- [RentOn-Audit-Performa-Pencarian-Kendaraan.md](RentOn-Audit-Performa-Pencarian-Kendaraan.md) — optimasi index database

**Belum pernah diuji end-to-end** di server live sepanjang seluruh pekerjaan konversi ini — wajib smoke-test manual (setidaknya: register, login, cari kendaraan, checkout, login admin) segera setelah instalasi pertama kali.
