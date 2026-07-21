# Audit Database & Query — Backend RentOn

**Tanggal:** 21 Juli 2026
**Cakupan:** 69 tabel database + seluruh 26 file model (`api`, `admin`, `agent`) + query di 24 controller REST
**Metodologi:** Pembacaan langsung source code & skema, bukan sekadar sampling — setiap pola dicek dengan grep menyeluruh lalu diverifikasi manual per kasus.

---

## Ringkasan Temuan

| Severity | Temuan |
|---|---|
| 🔴 Kritis | Tidak ada satu pun transaksi database (`trans_start`/`trans_complete`) dipakai di seluruh alur bisnis — semua operasi finansial multi-langkah berisiko *partial failure* |
| 🟠 Tinggi | Laporan PDF (`Report_m.php`) tidak punya `LIMIT` — rentan resource exhaustion untuk rentang tanggal lebar |
| 🟡 Sedang | N+1 query di `admin/Config.php::admins_get()` |
| 🟡 Sedang | Beberapa kolom relasi tanpa index (sudah didokumentasikan sebelumnya, dikonfirmasi ulang masih ada) |
| 🟢 Rendah | Anomali penamaan/referensi skema (sudah ada, dikonfirmasi belum diperbaiki) |
| ✅ Positif | **Tidak ditemukan SQL injection baru** di 24 controller hasil konversi REST — seluruh `where()`/`having()` sudah parameterized |

---

## 🔴 KRITIS — Tidak Ada Transaksi Database di Seluruh Alur Bisnis

**Temuan:** `grep -rl "trans_start\|trans_complete\|trans_begin"` di seluruh `application/modules`, `application/models`, `application/libraries` hanya menemukan pemakaian di **`Ion_auth_model.php`** (internal, untuk registrasi user) — **nol** pemakaian di kode bisnis manapun (booking, wallet, komisi, reward).

**Mengapa ini serius — 4 alur nyata yang terekspos:**

### 1. Checkout/booking (`RentVehicle.php::bookings_post`, api)
Urutan tanpa transaksi:
```
1. Customer_m->decrease_balance()       ← saldo customer dipotong
2. Basic_m->decrease_voucher_quota()    ← kuota voucher dikurangi (kalau pakai voucher)
3. RentVehicle_m->post_checkout()       ← insert transaction_rent_vehicle
4. RentVehicle_m->add_timeline_transaction()
```
Kalau server mati/PHP error/koneksi DB putus di antara langkah 1 dan 3 — **saldo customer sudah terpotong tapi booking tidak pernah tercatat**. Uang hilang tanpa jejak transaksi.

### 2. Penyelesaian booking (`PartnerRent.php::booking_done_put`, api)
Urutan lebih panjang tanpa transaksi: update status transaksi → insert timeline → insert point reward customer → insert point reward partner → loop cek & insert partner reward (bisa 0-N insert) → hitung & insert komisi agent → `increase_balance` ke partner. **7+ operasi write terpisah**, satu gagal di tengah = saldo partner/agent tidak konsisten dengan status transaksi yang sudah terlanjur "selesai".

### 3. Verifikasi topup (`admin/Customer.php::topup_status_put`)
Update status topup dan `increase_balance` customer adalah 2 query terpisah tanpa transaksi.

### 4. Pembatalan promosi (`PartnerRent.php::promotions_delete`)
Update status promosi + `increase_balance` (refund) — sama, 2 langkah lepas.

**Rekomendasi:** bungkus setiap alur di atas dengan:
```php
$this->db->trans_start();
// ...semua query langkah 1-N...
$this->db->trans_complete();
if ($this->db->trans_status() === FALSE) {
    return $this->fail('Terjadi kesalahan, transaksi dibatalkan', 500);
}
```
Ini perubahan yang cukup mekanis untuk diterapkan di ±8 method inti (checkout, cancel booking x2, booking done, topup verify, withdraw verify x2, promote cancel) — beri tahu saya kalau ingin saya kerjakan sekaligus.

---

## 🟠 TINGGI — Laporan PDF Tanpa Batas Baris

**Lokasi:** `application/modules/admin/models/Report_m.php` — seluruh 6 fungsi `get_*()` (agent_transaction, partner_transaction, topup, withdraw, partner_promote_transaction) tidak memanggil `->limit()` sama sekali, hanya difilter `start_date`/`end_date`/`ids`.

**Dampak:** Admin generate laporan rentang tanggal lebar (mis. 1 tahun) di database yang sudah punya ratusan ribu baris transaksi → seluruh dataset ditarik ke memori PHP sekaligus untuk dirender TCPDF → berpotensi *memory exhaustion* / request timeout / PDF gagal terbentuk.

**Rekomendasi:** tambah cap wajar (mis. hard limit 5.000-10.000 baris per laporan, atau paksa rentang tanggal maksimal 1-3 bulan per generate) dan tampilkan pesan ke admin kalau rentang terlalu lebar.

---

## 🟡 SEDANG — N+1 Query di Manajemen User Admin

**Lokasi:** [`admin/Config.php::admins_get()`](RentonBachkEnd-main/application/modules/admin/controllers/Config.php)
```php
foreach ($this->ion_auth->users()->result() as $user) {
    $groups = $this->ion_auth->get_users_groups($user->id)->result(); // 1 query per user
    ...
}
```
Untuk N user admin/staff, ini menjalankan 1 + N query (bukan 1 query dengan JOIN). Dampak kecil untuk sekarang (jumlah staff biasanya puluhan, bukan ribuan), tapi pola ini sebaiknya diganti 1 query `JOIN accounts_groups` + `GROUP_CONCAT` kalau daftar staff bertambah banyak.

---

## 🟡 SEDANG — Index yang Masih Hilang (Dikonfirmasi Ulang)

Sudah didokumentasikan di [RentOn-DDL-UML-Diagram-Flowchart-Backend.md](RentOn-DDL-UML-Diagram-Flowchart-Backend.md) §4, dicek ulang hari ini — **masih belum ada migration untuk ini** (beda dengan index pencarian kendaraan yang sudah dibuatkan migration terpisah):

| Tabel | Kolom | Dipakai di | Dampak |
|---|---|---|---|
| `notification` | `account_id` | Query notifikasi per akun (kalau diimplementasikan) | Full scan tiap ambil notifikasi user |
| `chat_message` | `account_id` | Filter pesan per pengirim | Full scan |
| `history_partner_reward` | `account_id`, `reward_id` | Cek klaim reward per akun (`is_reward_added` dipanggil di **loop** `_process_partner_rewards`!) | Ini dipanggil di dalam foreach per reward per scope — makin banyak reward aktif, makin banyak full-scan berulang di endpoint `booking_done_put` |
| `partner_rewards` | `feature_id`, `reward_scope`, `reward_type` | `list_reward()` dipanggil di dalam loop yang sama | Sama — dipanggil berulang di jalur "selesaikan booking", jalur yang justru paling sering dieksekusi |

⚠️ **Catatan penting:** kombinasi index hilang + dipanggil di dalam loop di `_process_partner_rewards()` (yang saya tulis sendiri saat migrasi ke REST, meniru logic asli apa adanya) berarti **setiap kali partner menyelesaikan booking**, aplikasi melakukan beberapa full-table-scan berulang ke `history_partner_reward` dan `partner_rewards`. Ini bukan bug baru (logic asli sudah begini), tapi sekarang lebih jelas terlihat dan layak diperbaiki bersamaan dengan poin transaksi database di atas karena berada di method yang sama persis.

**Migration index tambahan yang direkomendasikan:**
```sql
ALTER TABLE `notification` ADD KEY `notification_account_id` (`account_id`);
ALTER TABLE `chat_message` ADD KEY `chat_message_account_id` (`account_id`);
ALTER TABLE `history_partner_reward` ADD KEY `hpr_account_reward` (`account_id`,`reward_id`);
ALTER TABLE `partner_rewards` ADD KEY `pr_feature_scope` (`feature_id`,`reward_scope`);
```

---

## 🟢 RENDAH — Anomali Skema (Dikonfirmasi Belum Diperbaiki)

Sudah didokumentasikan sebelumnya, dicek ulang — statusnya **tetap sama**:
- `agent_withdraw.status` masih mereferensikan `customer_withdraw_status`, bukan `agent_withdraw_status` yang sudah dibuat khusus untuk tabel ini (tabel `agent_withdraw_status` tetap tidak terpakai).
- `partners.agent_id`, `customers.referal_id`, `partners.referal_id` masih tanpa `FOREIGN KEY` — integritas relasi ini sepenuhnya bergantung pada application logic, tidak dijamin database.
- Tabel `config` masih tanpa `PRIMARY KEY` sama sekali.

---

## ✅ Temuan Positif — Verifikasi Keamanan Query Pasca-Konversi REST

Saya cek ulang **seluruh** 24 controller hasil konversi REST (bukan cuma yang sudah diaudit sebelumnya) dengan pola pencarian SQL injection yang sama seperti audit keamanan awal (`where()`/`having()` dengan string concatenation langsung):

```
grep -rnE "->(where|having|or_where|or_having)\(['\"][^'\"]*['\"]\s*\.\s*\$" seluruh modul
```

**Hasil: nihil** di luar yang sudah diketahui & diperbaiki sebelumnya (`Rentvehicle_m.php`). Konversi ke REST — baik yang saya kerjakan langsung maupun yang didelegasikan ke agent — **tidak memperkenalkan celah SQL injection baru**. `db->query()` raw SQL juga hanya tersisa di `Chat_m.php` (sudah diaudit & dinilai aman karena `$account_id` bersumber dari server, bukan input user langsung — lihat [RentOn-Audit-Keamanan-Backend.md](RentOn-Audit-Keamanan-Backend.md) S3).

---

## Rencana Tindak Lanjut

| Prioritas | Tindakan | Terkait |
|---|---|---|
| 1 | Bungkus 8 alur finansial inti dengan `trans_start()`/`trans_complete()` | Temuan kritis §1 |
| 2 | Tambah index `history_partner_reward`, `partner_rewards`, `notification`, `chat_message` | Temuan sedang |
| 3 | Tambah cap baris/rentang tanggal di `Report_m.php` | Temuan tinggi |
| 4 | Refactor `admins_get()` jadi 1 query JOIN | Temuan sedang (dampak kecil, prioritas rendah) |
| 5 | Perbaiki anomali skema (`agent_withdraw_status`, FK `agent_id`/`referal_id`, PK tabel `config`) | Temuan rendah |

Beri tahu saya kalau mau saya langsung kerjakan salah satu (atau semua) dari daftar di atas — terutama poin 1 & 2 karena keduanya berada tepat di jalur transaksi uang yang paling sering dieksekusi (checkout & penyelesaian booking).
