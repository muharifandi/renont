# Laporan Audit & Perbaikan Performa — Fitur Pencarian Kendaraan (RentOn)

**Tanggal:** 19 Juli 2026
**Modul terdampak:** `RentonBachkEnd-main/application/modules/api/models/Rentvehicle_m.php` (endpoint `POST /api/RentVehicle/list`)
**Gejala awal:** Pencarian kendaraan terasa lambat, dan semakin lambat ketika beberapa filter dipakai bersamaan (multi-filter: harga, jumlah penumpang, kota, tanggal sewa).

---

## 1. Ringkasan Eksekutif

Setelah audit terhadap skema database dan query pencarian, ditemukan bahwa penyebab utama bukan pada logika filter itu sendiri, melainkan **kolom-kolom yang dipakai untuk filter sama sekali tidak memiliki index di database**, ditambah **2 bug pada query tanggal sewa** yang membuat MySQL tidak bisa memakai index sekalipun ditambahkan, dan sekaligus menghasilkan data yang salah. Seluruh temuan sudah diperbaiki pada kode dan disiapkan migration index untuk dijalankan di database.

---

## 2. Metodologi Investigasi

1. Membaca alur endpoint pencarian: `RentVehicle.php::list_post()` → `Rentvehicle_m.php::list_vehicle()` / `list_promote_vehicle()`.
2. Mengekstrak definisi tabel (`CREATE TABLE` + `ALTER TABLE ... ADD KEY`) dari dump database (`database/db_rentone_06_Dec_2021.sql`) untuk memeriksa index yang benar-benar ada di production, dibandingkan dengan kolom yang dipakai di klausa `WHERE`/`JOIN`.
3. Menelusuri setiap kondisi filter (`min_passenger`, `max_passenger`, `min_price`, `max_price`, `regencies`, `start_date`, `end_date`) satu per satu untuk melihat bagaimana masing-masing diterjemahkan ke SQL oleh CodeIgniter Query Builder.
4. Memverifikasi hasil query builder terhadap source `system/database/DB_query_builder.php` untuk memastikan bagaimana CI menyusun klausa WHERE dari raw string vs. pasangan key-value.

---

## 3. Penyebab (Root Cause)

### 3.1 Index database tidak ada untuk kolom yang difilter — *penyebab utama*

Dicek dari dump SQL, tabel-tabel berikut **tidak punya index** di kolom yang justru paling sering dipakai untuk filter pencarian:

| Tabel | Kolom | Dipakai untuk | Index sebelumnya |
|---|---|---|---|
| `rent_vehicles_item` | `status` | Selalu difilter (`status = 1`) di **setiap** pencarian | ❌ Tidak ada |
| `rent_vehicles_item` | `price` | Filter harga min/max | ❌ Tidak ada |
| `rent_vehicles_item` | `max_passenger` | Filter jumlah penumpang min/max | ❌ Tidak ada |
| `partners` | `regencies_id` | Filter kota/regency | ❌ Tidak ada |
| `transaction_rent_vehicle` | `start_date`, `end_date` | Filter ketersediaan tanggal sewa | ❌ Tidak ada |

Tanpa index, setiap kondisi WHERE di atas memaksa MySQL melakukan **full table scan**: membaca seluruh baris tabel lalu mencocokkan satu per satu di memori, bukan lompat langsung ke baris relevan lewat index lookup.

**Kenapa multi-filter terasa lebih lambat dari filter tunggal:** karena setiap kondisi tambahan (harga + penumpang + kota + tanggal sekaligus) sama-sama harus dievaluasi manual per baris tanpa index. Tidak ada "jalan pintas" yang mempersempit jumlah baris lebih awal, jadi biaya query kurang lebih menumpuk seiring jumlah filter aktif, alih-alih menjadi lebih cepat seperti yang biasanya diharapkan dari filter tambahan pada query yang ter-index dengan baik.

### 3.2 Filter tanggal sewa memakai `DATE(kolom)` — query menjadi *non-sargable*

**Lokasi:** `Rentvehicle_m.php` (subquery ketersediaan kendaraan)

```php
// SEBELUM
WHERE ... (DATE(end_date) >= '2026-01-01' AND DATE(start_date) <= '2026-01-05' OR ...)
```

Membungkus kolom `start_date`/`end_date` dengan fungsi `DATE(...)` membuat MySQL **tidak bisa memakai index apa pun** di kolom tersebut — bahkan jika index ditambahkan, index itu tetap tidak terpakai selama kolom masih dibungkus fungsi di sisi kiri perbandingan. Ini disebut query *non-sargable*.

### 3.3 Bug tersembunyi: subquery tanggal tidak punya `GROUP BY item_id`

**Lokasi:** subquery yang sama dengan poin 3.2

```php
SELECT COUNT(id) as number_book, item_id
FROM transaction_rent_vehicle
WHERE status != 8 AND status !=10 AND status !=11 AND status !=12 AND (...)
```

Tidak ada `GROUP BY item_id` pada subquery ini. Akibatnya subquery hanya mengembalikan **satu baris agregat global** — jumlah *seluruh* booking bentrok di *seluruh* kendaraan, dengan `item_id` yang nilainya tidak pasti (bergantung baris mana yang dipilih MySQL). Ini membuat proses JOIN ke `rent_vehicles_item.id` nyaris tidak pernah cocok dengan benar, sehingga filter "sembunyikan kendaraan yang sudah dibooking di rentang tanggal ini" secara fungsional rusak — bukan cuma soal performa, tapi juga soal korektnes hasil pencarian.

### 3.4 Join foto kendaraan menggandakan baris sebelum `GROUP BY`

**Lokasi:** `$this->db->join('rent_vehicles_item_images', ...)`

Setiap kendaraan bisa punya banyak foto (`rent_vehicles_item_images` adalah tabel one-to-many terhadap `rent_vehicles_item`). Join langsung ke tabel ini membuat 1 kendaraan dengan 5 foto menghasilkan 5 baris duplikat di hasil antara, sebelum akhirnya di-`GROUP BY rent_vehicles_item.id` lagi di akhir query. Semakin banyak foto per listing (data bertambah seiring waktu), semakin besar ledakan baris sementara ini — membebani proses join dan grouping tanpa manfaat.

### 3.5 (Temuan tambahan, terkait keamanan bukan performa) Filter numerik rawan SQL Injection

`min_passenger`, `max_passenger`, `min_price`, `max_price` sebelumnya di-concatenate mentah ke string SQL (`'kolom >= '.$param['min_price']`) tanpa escaping. Ini dibahas detail di audit sebelumnya — dibenahi sekalian karena berada di fungsi yang sama yang sedang dioptimasi.

---

## 4. Solusi yang Diterapkan

### 4.1 Menambahkan index database (perbaikan performa utama)

File baru: [`RentonBachkEnd-main/database/migration_2026_07_19_optimize_vehicle_search_index.sql`](RentonBachkEnd-main/database/migration_2026_07_19_optimize_vehicle_search_index.sql)

```sql
ALTER TABLE `rent_vehicles_item`
  ADD KEY `rent_vehicles_item_status` (`status`),
  ADD KEY `rent_vehicles_item_price` (`price`),
  ADD KEY `rent_vehicles_item_max_passenger` (`max_passenger`),
  ADD KEY `rent_vehicles_item_status_price` (`status`,`price`),
  ADD KEY `rent_vehicles_item_status_max_passenger` (`status`,`max_passenger`);

ALTER TABLE `partners`
  ADD KEY `partners_regencies_id` (`regencies_id`);

ALTER TABLE `transaction_rent_vehicle`
  ADD KEY `transaction_rent_vehicle_status_dates` (`status`,`start_date`,`end_date`);
```

Index composite `(status, price)` dan `(status, max_passenger)` ditambahkan karena `status = 1` **selalu** ikut di setiap pencarian bersamaan dengan filter harga/penumpang — composite index memungkinkan satu index lookup menangani kombinasi keduanya sekaligus.

> ⚠️ File ini belum otomatis dijalankan — perlu dieksekusi manual ke database production/staging, sebaiknya di luar jam trafik tinggi.

### 4.2 Memperbaiki filter tanggal agar sargable + benar

**File:** `Rentvehicle_m.php` (fungsi `list_vehicle()` dan `list_promote_vehicle()`)

```php
// SESUDAH
$start_date = $this->db->escape($param['start_date'].' 00:00:00');
$end_date = $this->db->escape($param['end_date'].' 23:59:59');
$this->db->join("(
    SELECT COUNT(id) as number_book, item_id
    FROM transaction_rent_vehicle
    WHERE status NOT IN (8,10,11,12)
    AND start_date <= $end_date AND end_date >= $start_date
    GROUP BY item_id
) transaction_rent_vehicle", 'transaction_rent_vehicle.item_id = rent_vehicles_item.id','left');
```

Perubahan:
- Kolom `start_date`/`end_date` dibandingkan langsung tanpa dibungkus `DATE()` → sekarang bisa memakai index baru di 4.1.
- Kondisi overlap 2-cabang OR disederhanakan jadi 1 kondisi interval-overlap yang setara secara logika, lebih murah dievaluasi optimizer.
- `GROUP BY item_id` ditambahkan → memperbaiki bug korektnes di poin 3.3, hasil sekarang benar per-kendaraan.
- Nilai tanggal di-escape lewat `$this->db->escape()` → menutup celah SQL injection di parameter ini.

### 4.3 Mencegah ledakan baris pada join foto

```php
// SESUDAH
$this->db->join('(
    SELECT item_id, MIN(id) as id, MIN(img) as img
    FROM rent_vehicles_item_images
    GROUP BY item_id
) rent_vehicles_item_images','rent_vehicles_item_images.item_id = rent_vehicles_item.id','left');
```

Agregasi 1-foto-per-kendaraan dilakukan lebih dulu di subquery, sehingga join ke tabel utama tidak lagi menggandakan baris berdasarkan jumlah foto.

### 4.4 Mengganti filter numerik ke parameterized query

```php
// SEBELUM
$this->db->where('rent_vehicles_item.max_passenger >= '.$param['min_passenger']);
// SESUDAH
$this->db->where('rent_vehicles_item.max_passenger >=', (int)$param['min_passenger']);
```

Diterapkan untuk `min_passenger`, `max_passenger`, `min_price`, `max_price` di kedua fungsi. Menutup celah SQL injection; hasil SQL yang dihasilkan tetap setara dan tetap kompatibel dengan index baru di 4.1.

---

## 5. Ringkasan Before / After

| # | Aspek | Sebelum | Sesudah | Dampak |
|---|---|---|---|---|
| 1 | Filter `status` / `price` / `max_passenger` | Full table scan | Index scan | Performa — dampak terbesar |
| 2 | Filter kota (`regencies_id`) | Full table scan di `partners` | Index scan | Performa |
| 3 | Filter tanggal sewa | Non-sargable (`DATE()`), tidak bisa index | Sargable, memakai index baru | Performa |
| 4 | Hasil filter tanggal sewa | Salah — agregat 1 baris global tanpa `GROUP BY` | Benar — 1 baris per kendaraan | Korektnes |
| 5 | Join foto kendaraan | Baris digandakan sebanyak jumlah foto | 1 baris per kendaraan sebelum join | Performa |
| 6 | Filter harga/penumpang | String SQL mentah (rawan SQL injection) | Parameterized, di-cast `(int)`/`(float)` | Keamanan |

---

## 6. Verifikasi yang Sudah Dilakukan

- `php -l` pada `Rentvehicle_m.php` → **lolos**, tidak ada syntax error.
- Perubahan diterapkan konsisten di kedua fungsi (`list_vehicle()` untuk hasil pencarian biasa, `list_promote_vehicle()` untuk item promosi) karena keduanya menduplikasi logika filter yang sama.

**Belum bisa diverifikasi di sesi ini** (tidak ada akses MySQL/PHP server live):
- Perbandingan waktu eksekusi query sebelum/sesudah lewat `EXPLAIN`.
- Pengujian fungsional end-to-end lewat aplikasi Android.

**Rekomendasi sebelum deploy ke production:**
1. Jalankan `migration_2026_07_19_optimize_vehicle_search_index.sql` di staging terlebih dahulu.
2. Bandingkan `EXPLAIN SELECT ...` pada query pencarian sebelum & sesudah index ditambahkan — pastikan `type` berubah dari `ALL` (full scan) menjadi `ref`/`range`.
3. Uji coba pencarian dengan kombinasi multi-filter (harga + penumpang + kota + tanggal) dari aplikasi untuk memastikan hasil tetap benar setelah perbaikan bug `GROUP BY`.
4. Pantau waktu respons endpoint `POST /api/RentVehicle/list` sebelum & sesudah rilis (mis. lewat log/APM) untuk mengukur dampak nyata di data production.

---

## 7. Belum Ditangani (Butuh Keputusan Lebih Lanjut)

- **Subquery rating/review** (`review_vehicle` + `transaction_rent_vehicle`) meng-agregasi ulang seluruh data di setiap pencarian, tanpa dibatasi filter apa pun. Solusi idealnya adalah **denormalisasi**: simpan `rating` & `total_review` langsung di `rent_vehicles_item`, di-update saat ada review baru. Perubahan ini lebih besar (menyentuh alur submit review) sehingga tidak dilakukan otomatis pada perbaikan ini.
- **`list_promote_vehicle()`** masih menjalankan 2 query `UPDATE` (aktivasi/expire status promosi) + `viewer+1` pada setiap request pencarian halaman pertama — bukan penyebab utama lambatnya pencarian, tapi menambah beban tiap request dan berisiko *lost update* saat trafik tinggi bersamaan. Direkomendasikan dipindah ke scheduled job terpisah.
