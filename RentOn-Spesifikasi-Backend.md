# Laporan Spesifikasi Backend — RentOn

**Tanggal pemeriksaan:** 21 Juli 2026
**Status backend:** REST API murni (baru selesai dikonversi dari web panel HTML session-based)
**Sumber:** `RentonBachkEnd-main/` — backup versi sebelum konversi tersedia di `RentonBachkEnd-main-backup-2026-07-19/`

---

## 1. Ringkasan Eksekutif

Backend ini adalah REST API untuk platform marketplace sewa kendaraan **RentOn**, melayani 3 klien: aplikasi mobile Android (customer & partner), backoffice admin, dan portal agent — ketiganya kini murni JSON API tanpa HTML sama sekali. Total **24 controller, 257 endpoint, 69 tabel database**.

**Yang sudah solid:** arsitektur REST konsisten, autentikasi terpadu berbasis token, seluruh kode lolos `php -l`.
**Yang masih jadi risiko:** sejumlah temuan keamanan kritis dari audit sebelumnya **belum diperbaiki** — lihat §5. Konversi ke REST tidak otomatis memperbaiki isu keamanan yang sifatnya independen dari transport layer.

---

## 2. Spesifikasi Teknis

| Komponen | Versi/Detail |
|---|---|
| Bahasa | PHP (lint diuji dengan PHP 8.4.8; **framework CodeIgniter 3.1.10 aslinya ditulis untuk PHP 5.6–7.x** — lihat catatan kompatibilitas di §7) |
| Framework | CodeIgniter 3.1.10 (EOL — tidak menerima update resmi lagi) + Modular Extensions (HMVC) |
| Database | MySQL — 69 tabel, lihat [RentOn-DDL-UML-Diagram-Flowchart-Backend.md](RentOn-DDL-UML-Diagram-Flowchart-Backend.md) |
| Auth library | Ion Auth (bcrypt/Argon2 via `password_hash()`) |
| REST library | `chriskacerguis/codeigniter-restserver`, dibungkus base class baru `REST_Base_Controller` |
| PDF generation | TCPDF 6.3.5 |
| Push notification | Firebase Cloud Messaging |
| Web server | Apache (berdasarkan `.htaccess` yang ada) |

### 2.1 Kompatibilitas PHP 8+ — Diverifikasi & Diperbaiki (21 Juli 2026)

CodeIgniter 3.1.10 dirilis Agustus 2020 (sebelum PHP 8 ada) sehingga **secara resmi hanya menargetkan PHP 5.6–7.4**. Backend ini sudah ditelusuri dan diperbaiki agar benar-benar jalan di **PHP 8.x terbaru** (diuji dengan PHP 8.4.8):

| Perbaikan | Detail |
|---|---|
| **Syntax error fatal dihapus** | `system/libraries/Profiler.php` memakai sintaks `$this->_compile_{$var}` (dynamic property via curly brace) — **dihapus total di PHP 8.0**, bikin seluruh aplikasi fatal error begitu Profiler diaktifkan. Diperbaiki jadi `$this->{'_compile_'.$var}`. |
| **Dynamic properties deprecation (PHP 8.2+)** | CodeIgniter 3 inti memakai dynamic property secara ekstensif (`$this->db`, `$this->load`, dst.) — akan memicu ratusan warning "Creation of dynamic property is deprecated" di PHP 8.2+. Diperbaiki dengan menambahkan atribut `#[AllowDynamicProperties]` ke 4 kelas basis: `CI_Controller`, `CI_Model`, `CI_Loader` (inti CodeIgniter), dan `MX_Controller` (akar sesungguhnya dari SEMUA controller di app ini, termasuk 24 controller REST yang baru). Atribut ini otomatis diwarisi ke seluruh turunannya. |
| Pemanggilan fungsi yang **dihapus di PHP 8** (`get_magic_quotes_gpc()`, `get_magic_quotes_runtime()`, `mcrypt_*`) | Diperiksa satu per satu — semua sudah dibungkus guard (`function_exists()`/`extension_loaded()`/`defined()`/version-check) yang membuatnya tidak pernah benar-benar terpanggil di PHP 8. Aman tanpa perlu diubah. |
| Curly-brace string offset (`$str{0}`, dihapus di PHP 8) | Tidak ditemukan di seluruh codebase. |

**Verifikasi yang dilakukan (bukan sekadar asumsi):**
1. Lint sweep menyeluruh `php -l` di **519 file PHP** (`system/` + `application/` + `application/third_party/`) dengan PHP 8.4.8 — ✅ **semua lolos, 0 syntax error**.
2. Uji runtime terisolasi: dibuktikan atribut `#[AllowDynamicProperties]` benar-benar menghilangkan deprecation warning (dites langsung, dibandingkan class dengan & tanpa atribut).
3. Verifikasi via `ReflectionClass` bahwa atribut benar-benar terpasang pada `CI_Controller` yang sesungguhnya (bukan cuma ada di source, tapi dikenali PHP engine).

**Kesimpulan:** backend ini sekarang **aman dijalankan di PHP 8.0 s.d. 8.4** (versi stabil terbaru saat ini) tanpa fatal error, dan tanpa banjir deprecation warning dari isu dynamic property. Yang **belum** diuji: eksekusi penuh end-to-end di server PHP+MySQL live sungguhan (lihat §7).

---

## 3. Arsitektur & Struktur Modul

```
application/modules/
├── auth/    (1 controller)  — login/logout/reset password terpusat, semua role non-mobile
├── api/     (9 controller)  — REST API aplikasi Android (customer & partner)
├── admin/   (10 controller) — REST API backoffice (menggantikan panel HTML lama)
└── agent/   (4 controller)  — REST API portal agent/marketing (menggantikan panel HTML lama)
```

**Autentikasi (terpadu, satu mekanisme untuk semua role):**
- Header HTTP `key` → divalidasi terhadap tabel `keys` → resolve akun & role via `accounts_groups`.
- Tidak ada lagi PHP session untuk admin/agent — sepenuhnya stateless, konsisten dengan mobile API.
- Role: `admin`(1), `supervisor`(2), `staff`(3), `partner`(4), `customer`(5), `reader`(6), `agent`(7).

**Format response standar di seluruh 257 endpoint:**
```json
{ "status": true|false, "message": "...", "data": {...}|[...]|null, "meta": {...}? }
```
Kode HTTP sesuai konteks (`200/201/400/401/402/403/404/409/422`), bukan selalu `200` seperti versi lama.

**Rincian lengkap 257 endpoint per modul:** lihat [RentOn-Konversi-REST-API.md](RentOn-Konversi-REST-API.md) §3.

---

## 4. Spesifikasi Database

- 69 tabel, dikelompokkan 10 domain fitur (Master Data, Akun, Customer, Partner, Agent, Kendaraan & Transaksi, Keuangan, Loyalty/Marketing, Engagement, Sistem).
- 75 `FOREIGN KEY` constraint eksplisit menjaga integritas relasional.
- DDL lengkap, ER diagram, dan flowchart 10 alur bisnis utama: [RentOn-DDL-UML-Diagram-Flowchart-Backend.md](RentOn-DDL-UML-Diagram-Flowchart-Backend.md).
- Index untuk kolom filter pencarian kendaraan (`status`, `price`, `max_passenger`, dsb.) sudah ditambahkan via migration terpisah — lihat [RentOn-Audit-Performa-Pencarian-Kendaraan.md](RentOn-Audit-Performa-Pencarian-Kendaraan.md). **Migration ini belum dieksekusi ke database** (baru berupa file `.sql`, perlu dijalankan manual).

---

## 5. Status Keamanan — Diverifikasi Ulang Hari Ini

Audit keamanan lengkap sebelumnya ada di [RentOn-Audit-Keamanan-Backend.md](RentOn-Audit-Keamanan-Backend.md) (16 temuan). Berikut status **terkini** (dicek ulang setelah konversi REST):

### ✅ Ikut Diperbaiki Selama Konversi REST
- SQL injection di filter pencarian kendaraan (`min/max_passenger`, `min/max_price`, `start/end_date`) — sudah parameterized sejak audit performa.
- Kebocoran nama pemilik akun di `check_email`/`check_phone` — sekarang hanya `available: bool`.
- `require_auth()` tidak lagi fatal-error pada key tidak valid.
- Unrestricted file upload **di `api/Customer.php` dan `api/Partner.php`** (dua titik upload customer/partner dari mobile) — sudah dibatasi ke `jpg|jpeg|png`.

### ✅ Diperbaiki 22 Juli 2026 (lihat [RentOn-Audit-Keamanan-REST-API.md](RentOn-Audit-Keamanan-REST-API.md))
- **Unrestricted file upload** — 5 titik tersisa (`admin/PartnerReward.php`, `admin/News.php`, `admin/Agent.php`, `agent/Partner.php`, `agent/Config.php`) semua dibatasi ke `jpg|jpeg|png` — kini 7 dari 7 titik upload sudah dibatasi.
- Folder `data/` tanpa proteksi eksekusi PHP — `.htaccess` baru dibuat, menonaktifkan eksekusi PHP di seluruh folder ini. Kombinasi RCE (upload wildcard + folder tereksekusi) **sudah tertutup**.
- Ditambah, di luar 16 temuan awal: 9 celah IDOR/BOLA finansial (termasuk pencurian saldo lewat pembatalan promosi), nol rate-limiting di seluruh API, dan API key yang tak pernah kedaluwarsa — ketiganya ditemukan & diperbaiki dalam audit terpisah khusus arsitektur REST.

### 🔴 Masih Belum Diperbaiki (Perlu Tindakan Terpisah)
| Temuan | Status | Lokasi |
|---|---|---|
| Folder `database/` bisa diunduh publik (tanpa `.htaccess`) | ⚠️ Belum diperbaiki | `RentonBachkEnd-main/database/` |
| Kredensial database production hardcoded di repo | ⚠️ Belum diperbaiki | `application/config/production/database.php` |
| `mailtest.php` — file debug tertinggal di web root | ⚠️ **Masih ada** | `RentonBachkEnd-main/mailtest.php` |
| `encryption_key` kosong | ⚠️ Belum diperbaiki | `application/config/production/config.php` |
| `csrf_protection` dinonaktifkan | ℹ️ Relevansi berkurang — API sekarang stateless (auth via header `key`, bukan cookie session), tapi tetap sebaiknya tidak dibiarkan `FALSE` tanpa alasan eksplisit | sama |
| Cookie `secure`/`httponly` mati | ℹ️ Relevansi berkurang untuk API, tapi bila ada endpoint yang masih pakai session (tidak ada saat ini) tetap berisiko | sama |

**Kesimpulan §5 (diperbarui):** Celah RCE utama (unrestricted upload + folder data tanpa proteksi eksekusi) **sudah tertutup sepenuhnya** per 22 Juli 2026 — 7 dari 7 titik upload dibatasi, dan `data/.htaccess` memblokir eksekusi PHP sebagai lapisan kedua. Yang tersisa sekarang murni item konfigurasi/hardening (kredensial, `mailtest.php`, `encryption_key`) — tidak ada lagi kombinasi yang langsung mengarah ke RCE.

---

## 6. Status Performa

Lihat detail lengkap di [RentOn-Audit-Performa-Pencarian-Kendaraan.md](RentOn-Audit-Performa-Pencarian-Kendaraan.md). Ringkasan:
- Akar masalah lambatnya pencarian kendaraan: index database yang hilang pada kolom filter utama — **sudah diperbaiki di level kode query**, tapi **index barunya (`migration_2026_07_19_optimize_vehicle_search_index.sql`) belum dieksekusi ke database**.
- Bug korektnes filter tanggal sewa (subquery tanpa `GROUP BY`) — sudah diperbaiki.
- Side-effect write pada endpoint pencarian (update status promosi + viewer count di setiap request) — belum ditangani, direkomendasikan dipindah ke scheduled job.

---

## 7. Kesiapan Deployment & Gap yang Tersisa

| Item | Status |
|---|---|
| Seluruh 24 controller lolos `php -l` | ✅ |
| Setiap pemanggilan method model diverifikasi terhadap source model | ✅ |
| **Pengujian runtime (request sungguhan ke server+DB live)** | ❌ **Belum pernah dilakukan** — tidak ada akses server PHP+MySQL live selama seluruh sesi kerja ini |
| Migration index database dieksekusi | ❌ Belum |
| Perbaikan keamanan §5 (upload wildcard, folder data, kredensial, mailtest.php) | ❌ Belum |
| CORS untuk frontend baru | ❌ Belum dikonfigurasi |
| Rate limiting (`rest_enable_limits`) | ❌ Masih nonaktif |
| Aplikasi Android menyesuaikan kontrak API baru | ❌ Belum (breaking change disengaja) |
| Frontend baru untuk admin/agent (React/Vue/dll) | ❌ Belum dibangun — backend sekarang API-only |
| Dokumentasi payload/response detail versi REST baru | ❌ Belum ditulis ulang (dokumen lama sudah usang) |
| Kompatibilitas CodeIgniter 3.1.10 dengan PHP versi produksi | ⚠️ Perlu dicek manual — CI3 dirancang untuk PHP lama, pastikan versi PHP di server production kompatibel |

---

## 8. Dokumen Terkait (Referensi Lengkap)

| Dokumen | Isi |
|---|---|
| [RentOn-Konversi-REST-API.md](RentOn-Konversi-REST-API.md) | Detail arsitektur REST baru + peta 257 endpoint |
| [RentOn-Audit-Keamanan-Backend.md](RentOn-Audit-Keamanan-Backend.md) | 16 temuan keamanan asli (lihat §5 di atas untuk status terkini) |
| [RentOn-Audit-Performa-Pencarian-Kendaraan.md](RentOn-Audit-Performa-Pencarian-Kendaraan.md) | Analisis & perbaikan performa pencarian kendaraan |
| [RentOn-DDL-UML-Diagram-Flowchart-Backend.md](RentOn-DDL-UML-Diagram-Flowchart-Backend.md) | Skema database lengkap, ER diagram, flowchart 10 alur bisnis |
| [RentOn-API-Endpoint-Documentation.md](RentOn-API-Endpoint-Documentation.md) / `-EN.md` | ⚠️ **Sudah usang** — mendokumentasikan endpoint versi lama (pre-konversi) |

---

## 9. Rekomendasi Prioritas

1. **Sebelum apa pun lain**: perbaiki 5 titik upload wildcard tersisa (§5) + tambahkan `.htaccess` blok eksekusi PHP di `data/` — ini kombinasi RCE yang masih benar-benar terbuka.
2. Jalankan migration index database.
3. Cabut kredensial database dari repo, hapus `mailtest.php`, proteksi folder `database/`.
4. Uji seluruh 257 endpoint di staging dengan server live sebelum menyentuh production.
5. Baru setelah itu: bangun frontend baru, update aplikasi Android, tulis ulang dokumentasi endpoint detail.
