# Audit Keamanan — Arsitektur REST API (Pasca-Konversi)

**Tanggal:** 21 Juli 2026 · **Diperbarui:** 21 Juli 2026 — seluruh temuan di bawah **sudah diperbaiki**, kecuali CORS (sengaja ditunda, lihat catatannya).
**Cakupan:** Arsitektur REST baru saja — mekanisme auth token (`REST_Base_Controller`), otorisasi per-role, dan **otorisasi per-objek (IDOR/BOLA)** di 24 controller hasil konversi (`api`, `admin`, `agent`, `auth`). Ini BUKAN pengulangan [RentOn-Audit-Keamanan-Backend.md](RentOn-Audit-Keamanan-Backend.md) (16 temuan lama, arsitektur session-based) — laporan itu tetap berlaku untuk temuan yang belum ada di sini (kredensial hardcoded, `mailtest.php`, dsb).
**Metodologi:** Baca langsung source code tiap endpoint yang menerima `{id}` dari URL/body, verifikasi apakah ada pengecekan kepemilikan (`resource->account_id === auth_account->id`) sebelum baca/ubah/hapus data — bukan asumsi dari nama method.

---

## Ringkasan Temuan & Status Perbaikan

| Severity | Temuan | Jumlah | Status |
|---|---|---|---|
| 🔴 **KRITIS** | IDOR pada endpoint finansial — bisa mencuri saldo & menyelesaikan transaksi orang lain | 3 endpoint | ✅ Diperbaiki |
| 🔴 Kritis | Tidak ada rate limiting / brute-force protection sama sekali di seluruh API (termasuk login) | Global | ✅ Diperbaiki |
| 🟠 Tinggi | IDOR pada endpoint booking — baca/hapus/ubah data milik akun lain | 6 endpoint | ✅ Diperbaiki |
| 🟠 Tinggi | 5 endpoint upload file masih `allowed_types='*'` | 5 file | ✅ Diperbaiki |
| 🟡 Sedang | IDOR lama di `Customer.php` (bank/topup) yang terwarisi dari sebelum konversi | 4 endpoint | ✅ Diperbaiki |
| 🟡 Sedang | API key tidak pernah kedaluwarsa (no expiry) | Global | ✅ Diperbaiki |
| 🟡 Sedang | CORS non-aktif di semua environment — akan memblokir admin frontend baru berbasis browser | Global | ⏸ Sengaja ditunda |
| ✅ Positif | Tidak ada mass-assignment — semua field diekstrak eksplisit, tidak ada bulk `$this->put()`/`$this->post()` ke DB | — | — |
| ✅ Positif | Modul `agent` menerapkan pengecekan kepemilikan dengan benar & konsisten di semua endpoint | — | — |

**Aksi manual yang masih perlu dilakukan sebelum deploy:** jalankan migration baru `database/migration_2026_07_21_add_rate_limit_and_key_expiry.sql` (menambah tabel `limits` + kolom `keys.date_expires`) terhadap database live — belum dieksekusi.

---

## 🔴 KRITIS #1 — IDOR di Endpoint Finansial (Pencurian Saldo & Manipulasi Transaksi) — ✅ DIPERBAIKI

**Fix:** ketiga method di bawah kini mengecek kepemilikan sebelum bertindak (pola disalin dari `bookings_delete()` yang sudah benar), dan `booking_done_put()` menambah guard `status === 8 → 409 Conflict` untuk mencegah pemrosesan reward/komisi ganda kalau dipanggil dua kali. Constructor `PartnerRent.php` juga diubah menjadi `require_auth_group([self::GROUP_PARTNER])` di seluruh 17 endpoint (sebelumnya hanya `require_auth()` — cek key valid tanpa cek role).

Tiga endpoint di [`api/PartnerRent.php`](RentonBachkEnd-main/application/modules/api/controllers/PartnerRent.php) memanggil `require_auth()` (memvalidasi key valid) tapi **tidak pernah memverifikasi bahwa resource yang diakses benar-benar milik akun yang login**. Karena `require_auth()` menerima key dari role manapun (partner, customer, bahkan agent), siapa pun yang punya key valid bisa mengeksploitasi ini terhadap transaksi/promosi milik akun lain.

### 1a. `PUT api/partnerRent/booking_done/{id}` — `booking_done_put()` (baris 376)
```php
public function booking_done_put($id = null)
{
    $account = $this->require_auth();   // ← hanya cek key valid, TIDAK cek kepemilikan
    ...
    $transaction_detail = $this->PartnerRent_m->transaction_detail($id);   // fetch by ID mentah
    ...
    $this->Customer_m->increase_balance($vehicle->account_id, $transaction_detail->total_payment - $transaction_detail->admin_fee);
    // ^ kredit saldo ke pemilik ASLI kendaraan (vehicle->account_id) — bukan ke $account->id, jadi
    //   di endpoint ini kreditnya ke pihak yang benar, TAPI siapapun bisa MEMICU pelunasan
    //   transaksi orang lain kapan saja, sebelum waktunya, sekaligus memicu reward poin & komisi agent.
}
```
**Dampak:** siapa pun dengan API key valid (termasuk akun customer biasa) bisa memanggil endpoint ini dengan ID transaksi siapa saja untuk: memaksa transaksi berstatus "selesai" sebelum sewa berakhir, mencairkan saldo ke partner lebih cepat dari seharusnya, memicu bonus poin ganda (dengan memanggil berkali-kali sebelum status berubah — lihat §2 di bawah untuk race condition terkait), dan memicu perhitungan komisi agent. Method sibling di file yang sama (`bookings_delete`, baris 295) **sudah benar** mengecek `vehicle->account_id !== account->id`, jadi ini murni celah yang lolos, bukan pola yang belum dikenal.

### 1b. `DELETE api/partnerRent/promotions/{id}` — `promotions_delete()` (baris 624) — **PALING PARAH**
```php
public function promotions_delete($id = null)
{
    $account = $this->require_auth();
    $promote = $this->PartnerRent_m->promote_detail($id);   // fetch by ID mentah, tanpa cek pemilik
    ...
    $this->PartnerRent_m->update_promote($id, ['canceled_total_return' => $total_return, 'status' => 3]);
    $this->Customer_m->increase_balance($account->id, $total_return);   // ← dikredit ke PENYERANG, bukan pemilik promosi!
}
```
**Dampak — pencurian saldo langsung:** Attacker cukup login sebagai partner mana saja (akunnya sendiri, sah), lalu tebak/enumerasi ID promosi (`item_id` di tabel `rent_vehicle_item_promote`, integer berurutan — mudah ditebak), lalu panggil `DELETE api/partnerRent/promotions/{id_milik_partner_lain}`. Promosi milik partner lain otomatis dibatalkan, **dan nilai refund-nya masuk ke saldo attacker**, bukan ke pemilik promosi yang sah. ini bug pencurian uang nyata (real money-theft bug), bukan sekadar exposure data.

### 1c. `PUT api/partnerRent/booking_status/{id}` — `booking_status_put()` (baris 332)
Tidak ada cek kepemilikan sama sekali (beda dengan `bookings_delete` di file yang sama yang sudah benar). Siapa pun bisa mengubah status transaksi rental milik partner lain ke status apa pun secara sepihak — termasuk kemungkinan set langsung ke status yang seharusnya hanya tercapai lewat `booking_done_put`, memicu efek samping notifikasi ke pelanggan yang salah/menyesatkan.

**Rekomendasi (berlaku utk ketiganya):** tambahkan pengecekan kepemilikan persis seperti yang sudah benar di `bookings_delete()` — pattern siap pakai yang tinggal disalin:
```php
$transaction_detail = $this->PartnerRent_m->transaction_detail($id);
if (!$transaction_detail) return $this->not_found('Transaksi tidak ditemukan');
$vehicle = $this->RentVehicle_m->vehicle_detail($transaction_detail->item_id);
if ((int) $vehicle->account_id !== (int) $account->id) {
    return $this->forbidden();
}
```
Untuk `promotions_delete`, tambahkan cek yang sama terhadap `$promote->account_id`.

*(Fix di atas sudah diterapkan persis seperti rekomendasi.)*

---

## 🔴 KRITIS #2 — Nol Rate Limiting / Brute-Force Protection di Seluruh API — ✅ DIPERBAIKI

**Fix:** `track_login_attempts = TRUE` diaktifkan di ketiga environment `ion_auth.php` — Ion Auth kini benar-benar mengunci akun setelah 3 percobaan gagal selama 600 detik (`maximum_login_attempts`/`lockout_time` sudah terkonfigurasi sebelumnya tapi tidak aktif). Ini melindungi KEDUA jalur login (`Auth::login_post` untuk admin/staff/agent, dan `Customer::login_post` — keduanya berujung ke `Ion_auth_model::login()` yang sama). Sebagai lapisan tambahan per-IP (independen dari identity), `rest_enable_limits = TRUE` + `rest_limits_method = 'IP_ADDRESS'` diaktifkan khusus di modul `api` dan `auth`, dengan anotasi `protected $methods = ['login_post' => ['limit' => 10, 'time' => 300]]` di kedua controller login — maksimum 10 percobaan/5 menit per alamat IP, terlepas dari akun mana yang ditarget. Tabel `limits` (belum ada di skema manapun) ditambahkan lewat migration baru.

Dikonfirmasi dari konfigurasi, bukan asumsi:
```php
// application/modules/{api,admin,agent,auth}/config/rest.php — SEMUA modul, semua environment:
$config['rest_enable_limits'] = FALSE;

// application/config/{development,testing,production}/ion_auth.php:
$config['track_login_attempts'] = FALSE;   // lockout Ion Auth juga tidak aktif meski
                                            // maximum_login_attempts=3 & lockout_time=600 sudah dikonfigurasi
```
**Dampak:** `POST auth/login` (admin/staff/agent) dan `POST api/customer/login` bisa di-brute-force tanpa batas — tidak ada penguncian akun, tidak ada throttle per-IP maupun per-key. Ini berlaku untuk SEMUA endpoint API, bukan cuma login (jadi endpoint apa pun bisa di-hammer tanpa batas, termasuk yang di §1 di atas — memudahkan enumerasi ID transaksi/promosi untuk mengeksploitasi IDOR).

*(Fix di atas sudah diterapkan.)*

---

## 🟠 TINGGI — IDOR pada Data Booking (Baca/Tulis Data Milik Akun Lain) — ✅ DIPERBAIKI

**Fix:** kedelapan endpoint di bawah kini mengecek kepemilikan sebelum baca/tulis. `CustomerRent.php` dan `PartnerRent.php` juga diubah menjadi `require_auth_group([self::GROUP_CUSTOMER])`/`require_auth_group([self::GROUP_PARTNER])` di seluruh method-nya (sebelumnya `require_auth()` polos, jadi akun role apa pun bisa memanggil endpoint manajemen kendaraan/booking milik role lain).

| Endpoint | File | Masalah |
|---|---|---|
| `GET api/customerRent/bookings/{id}` | `CustomerRent.php::_booking_detail()` (baris 45) | Detail transaksi (data kendaraan, partner, harga, saldo) diambil hanya berdasar `$id`, tanpa cek `transaction_detail->account_id === $account->id`. Sibling method `bookings_delete`/`booking_status_put` di file yang sama sudah benar mengecek ini. |
| `POST api/customerRent/booking_review/{id}` | `CustomerRent.php::booking_review_post()` (baris 161) | Tidak cek transaksi ada/milik siapa — bisa post ulasan atas nama transaksi ID sembarang. |
| `GET api/partnerRent/vehicles/{id}` | `PartnerRent.php::vehicles_get()` (baris 118) | Detail kendaraan partner lain (termasuk data non-publik) bisa diambil oleh partner mana pun yang login. |
| `PUT api/partnerRent/vehicles/{id}` | `PartnerRent.php::vehicles_put()` (baris 103) | Partner A bisa mengubah data kendaraan milik Partner B — harga, ketersediaan, deskripsi. |
| `DELETE api/partnerRent/vehicles/{id}` | `PartnerRent.php::vehicles_delete()` (baris 153) | Partner A bisa **menghapus** kendaraan milik Partner B. |
| `DELETE api/partnerRent/vehicle_photos/{id}` | `PartnerRent.php::vehicle_photos_delete()` (baris 192) | Partner A bisa menghapus foto kendaraan milik Partner B (termasuk `unlink()` file fisik di server). |
| `GET api/partnerRent/bookings/{id}` | `PartnerRent.php::_booking_detail()` (baris 257) | Sama seperti CustomerRent — data pelanggan & transaksi partner lain bisa dibaca. |
| `POST api/partnerRent/booking_review/{id}` | `PartnerRent.php::booking_review_post()` (baris 537) | Sama seperti CustomerRent — post ulasan atas nama transaksi ID sembarang. |

Catatan penting: seluruh endpoint di atas **tidak dibatasi role** juga — `PartnerRent.php` tidak memanggil `require_group([self::GROUP_PARTNER])` di satu pun method-nya (dikonfirmasi: 17 pemanggilan `require_auth()`, 0 pemanggilan `require_group()` di file ini). Jadi bahkan akun **customer** yang punya key valid bisa memanggil endpoint manajemen kendaraan partner ini, bukan cuma partner lain.

*(Fix di atas sudah diterapkan — termasuk `require_group([GROUP_PARTNER])` di seluruh `PartnerRent.php`, dikonfirmasi tidak ada endpoint customer yang ikut ter-block karena seluruh file memang murni partner-facing.)*

---

## 🟡 SEDANG — IDOR Lama yang Tetap Terwarisi (Bukan Regresi Baru) — ✅ DIPERBAIKI

Dua endpoint di [`api/Customer.php`](RentonBachkEnd-main/application/modules/api/controllers/Customer.php) **sudah punya bug IDOR yang sama sejak sebelum konversi** (diverifikasi dengan membandingkan ke `RentonBachkEnd-main-backup-2026-07-19` — controller lama `bank_detail_post()`/`delete_bank_post()` punya persis pola yang sama, `$id` dari POST tanpa cek pemilik). Konversi REST **mewarisi**, bukan memperkenalkan, bug ini:

| Endpoint | Model dipanggil | Masalah |
|---|---|---|
| `GET api/customer/bank/{id}` | `Customer_m::bank_detail($id)` | Tidak filter `account_id` — bisa lihat no. rekening bank customer lain. |
| `DELETE api/customer/bank/{id}` | `Customer_m::delete_bank($id)` | Tidak filter `account_id` — bisa hapus rekening bank customer lain. |
| `GET api/customer/topups/{id}` | `Customer_m::topup_detail($id)` | Tidak filter `account_id` — bisa lihat detail topup customer lain. |
| `POST api/customer/topup_proof/{id}` | `Customer_m::update_topup($id, ...)` | Tidak filter `account_id` — bisa upload bukti transfer & ubah status topup customer lain. |

**Fix:** keempat model method (`bank_detail`, `delete_bank`, `topup_detail`, `update_topup`) diberi parameter `$account_id = null` opsional — kalau diisi, query menambah `where('account_id', $account_id)`. Dibuat opsional (bukan wajib) karena `topup_detail()` juga dipakai `admin/Customer.php` untuk verifikasi topup lintas-akun oleh staff — itu tetap memanggil tanpa parameter kedua (unrestricted, sesuai wewenang admin), sedangkan `api/Customer.php` sekarang selalu mengirim `$account->id`. Dikonfirmasi model `admin` module punya file `Customer_m.php` terpisah sendiri, jadi perubahan ini tidak memengaruhi verifikasi topup oleh admin.

---

## 🟡 SEDANG — API Key Tidak Pernah Kedaluwarsa — ✅ DIPERBAIKI

Skema tabel `keys` (dikonfirmasi dari dump SQL) hanya punya `date_created`, tidak ada `date_expires`/`last_used_at`. `require_auth()` di `REST_Base_Controller.php` hanya mengecek `level != 0`, tidak pernah mengecek umur token. Kombinasikan dengan temuan lama "logout hanya set `level=0`" — key yang bocor (lewat log, MITM di HTTP non-TLS, atau device yang hilang) **tetap valid selamanya** sampai user logout manual.

**Fix:** kolom `date_expires` ditambahkan ke tabel `keys` via migration (nullable, supaya key lama yang sudah terlanjur ada tetap valid — backward compatible). `MY_Api::_insert_key()` kini otomatis mengisi `date_expires = now + 30 hari` untuk setiap key baru. `REST_Base_Controller::require_auth()` menolak (401) key yang `date_expires`-nya sudah lewat, tapi tetap menerima key lama yang `date_expires`-nya `NULL` (grandfathered). Logika "reuse existing key" di `Auth::login_post` dan `Customer::login_post` diperbarui supaya hanya reuse key yang belum expired, dan expiry-nya diperpanjang 30 hari lagi setiap kali dipakai login ulang — jadi akun yang login rutin tidak akan pernah ter-lockout tiba-tiba.

---

## 🟡 SEDANG — CORS Nonaktif di Semua Environment (Bukan Kerentanan, Tapi Akan Memblokir Admin Frontend Baru)

```php
$config['check_cors'] = FALSE;   // sama persis di api, admin, agent, auth × development/testing/production
```
Ini **aman** untuk mobile app (tidak butuh CORS) dan justru default paling aman untuk API. Tapi karena Anda menyebutkan UI admin akan diganti total dengan framework baru — kalau framework itu berbasis browser (SPA React/Vue dsb. yang manggil API dari domain berbeda via `fetch`/`XHR`), browser AKAN memblokir semua request karena tidak ada header `Access-Control-Allow-Origin`. Ini bukan temuan keamanan, tapi item konfigurasi yang wajib disiapkan sebelum admin frontend baru bisa jalan.

**Rekomendasi:** set `check_cors = TRUE` + `allowed_cors_origins = ['https://domain-admin-baru.anda']` khusus di modul `admin`/`agent`/`auth` (jangan `allow_any_cors_domain = TRUE` supaya tidak membuka API ke origin sembarang).

**Status: sengaja belum dikerjakan** — origin domain admin frontend baru belum ditentukan, jadi `allowed_cors_origins` belum bisa diisi dengan benar. Aktifkan begitu domain/URL frontend baru sudah pasti — beri tahu saya domainnya kapan saja dan ini config-only, cepat dikerjakan.

---

## 🟠 TINGGI — Upload Wildcard — ✅ DIPERBAIKI

Sudah didokumentasikan di [RentOn-Spesifikasi-Backend.md](RentOn-Spesifikasi-Backend.md) §5 — dicek ulang hari ini, statusnya **persis sama, belum ada yang diperbaiki**:
```
admin/controllers/News.php:83           $config['allowed_types'] = '*';
admin/controllers/Agent.php:97          $config['allowed_types'] = '*';
admin/controllers/PartnerReward.php:85  $config['allowed_types'] = '*';
agent/controllers/Partner.php:50        'allowed_types' => '*',
agent/controllers/Config.php:52         'allowed_types' => '*',
```
Endpoint ini menerima file APA PUN (termasuk `.php`) dan menyimpannya di folder web-accessible (`data/...`) — potensi remote code execution kalau folder `data/` tidak diblok eksekusi PHP-nya oleh web server. **Prioritas tinggi** karena kombinasinya (upload sembarang tipe + folder web-accessible) adalah RCE klasik.

**Fix:** kelima `allowed_types` diganti ke `'jpg|jpeg|png'` (dikonfirmasi: kelima handler ini memang hanya memproses gambar — semuanya memanggil `thumb_image()`/`resize_image()`/`image_lib` GD setelah upload, jadi whitelist ini tidak menghilangkan fungsi apa pun yang sebelumnya benar-benar jalan untuk tipe file lain). Sebagai lapisan pertahanan kedua, `data/.htaccess` baru dibuat (sebelumnya tidak ada sama sekali) yang menonaktifkan eksekusi PHP di seluruh folder `data/` — jadi bahkan kalau ada bug upload lain yang belum ditemukan, file `.php` yang berhasil ter-upload tidak akan pernah bisa dieksekusi oleh web server.

---

## ✅ Positif #1 — Modul `agent` Menerapkan Otorisasi dengan Benar

Sebagai pembanding langsung: [`agent/controllers/Config.php::bank_get/bank_put/bank_delete`](RentonBachkEnd-main/application/modules/agent/controllers/Config.php) dan [`agent/controllers/Partner.php::index_get`](RentonBachkEnd-main/application/modules/agent/controllers/Partner.php) **konsisten** meneruskan `$account->id` ke setiap pemanggilan model (`get_bank($account->id, $id)`, `edit_bank($account->id, $id, ...)`) dan bahkan punya komentar eksplisit yang menjelaskan kenapa pengecekan kepemilikan tambahan diperlukan:
```php
// Partner_m::detail() only verifies the account is a partner account,
// not that it belongs to this agent -- enforce that ownership here.
```
Ini membuktikan pattern yang benar sudah diketahui & diterapkan — celah di §1/§2/§3 di atas adalah inkonsistensi penerapan pada `api` module, bukan ketidaktahuan pola. Rekomendasi perbaikan tinggal menyalin pattern yang sudah terbukti benar dari modul `agent` ini.

## ✅ Positif #2 — Tidak Ada Mass Assignment

`grep` untuk pemakaian `$this->put()`/`$this->post()`/`$this->delete()` tanpa argumen (yang akan mengambil SELURUH body request sekaligus) di seluruh 24 controller: **nihil**. Setiap endpoint mengekstrak field satu per satu (`$this->put('nama_field')`) sebelum diteruskan ke model — artinya klien tidak bisa menyisipkan field tak terduga (mis. `is_admin=1`, `status=verified`) ke dalam query INSERT/UPDATE. Praktik ini konsisten di semua modul.

---

## Rencana Tindak Lanjut — Status Akhir

| Prioritas | Tindakan | Status |
|---|---|---|
| 1 | Cek kepemilikan di 3 endpoint finansial kritis (`booking_done_put`, `promotions_delete`, `booking_status_put`) | ✅ Selesai |
| 2 | Cek kepemilikan di 5 endpoint booking lain + `require_group([GROUP_PARTNER/CUSTOMER])` | ✅ Selesai |
| 3 | Aktifkan `rest_enable_limits` + `track_login_attempts` | ✅ Selesai |
| 4 | Perbaiki 4 IDOR lama di `Customer.php` (bank/topup) | ✅ Selesai |
| 5 | Ganti 5 `allowed_types='*'` jadi whitelist + `data/.htaccess` blokir eksekusi PHP | ✅ Selesai |
| 6 | Tambah `date_expires` ke tabel `keys` + cek di `require_auth()` | ✅ Selesai |
| 7 | Aktifkan CORS khusus origin admin frontend baru | ⏸ Menunggu domain frontend baru ditentukan |

### ⚠️ Wajib dijalankan sebelum deploy

Migration baru **belum dieksekusi ke database manapun**:
```bash
mysql -u root -p rentone < RentonBachkEnd-main/database/migration_2026_07_21_add_rate_limit_and_key_expiry.sql
```
Ini menambahkan tabel `limits` (dipakai mekanisme rate-limit) dan kolom `keys.date_expires` (dipakai mekanisme key-expiry). Tanpa migration ini, rate limiting pada endpoint login akan gagal dengan error SQL "table 'limits' doesn't exist" begitu ada percobaan login — **jalankan migration ini sebelum kode yang sudah diperbaiki di-deploy**.

### File yang diubah dalam perbaikan ini
- `application/modules/api/controllers/PartnerRent.php`, `CustomerRent.php`, `Customer.php`
- `application/modules/api/models/Customer_m.php`
- `application/modules/auth/controllers/Auth.php`
- `application/modules/admin/controllers/News.php`, `Agent.php`, `PartnerReward.php`
- `application/modules/agent/controllers/Partner.php`, `Config.php`
- `application/libraries/REST_Base_Controller.php`, `MY_Api.php`
- `application/modules/api/config/rest.php`, `application/modules/auth/config/rest.php`
- `application/config/{development,testing,production}/ion_auth.php`
- `data/.htaccess` (baru)
- `database/migration_2026_07_21_add_rate_limit_and_key_expiry.sql` (baru, belum dieksekusi)
