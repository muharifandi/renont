# Rekap Pengalaman Kerja — Proyek "RentOn" (Aplikasi Sewa Kendaraan)

## 1. Ringkasan Proyek

**RentOn** adalah platform marketplace sewa kendaraan (mobil/motor) yang mempertemukan tiga aktor utama:

- **Customer** — pengguna yang mencari & menyewa kendaraan
- **Partner** — pemilik/penyedia kendaraan (mitra rental)
- **Agent** — agen yang merekrut partner dan mendapat komisi

Proyek terdiri dari 3 komponen utama yang saling terhubung melalui satu REST API:

| Komponen | Folder | Teknologi |
|---|---|---|
| Backend + Admin Backoffice + REST API | `RentonBachkEnd-main` | PHP (CodeIgniter 3) |
| Database | `RentonBachkEnd-main/database` | MySQL (±65 tabel) |
| Aplikasi Mobile Android | `Renton-App-master` | Java (Android native) |

---

## 2. Peran & Kontribusi Personal

Berdasarkan keterangan tambahan, peran yang dijalankan mencakup siklus penuh dari discovery hingga deployment & pembentukan tim:

1. **Requirement Gathering** — diundang berdiskusi langsung dengan owner project untuk menggali kebutuhan bisnis.
2. **Prototyping** — membuat prototype aplikasi untuk divalidasi owner sebelum masuk tahap development.
3. **System Design** — menyusun **flowchart alur sistem** (customer flow, partner flow, agent flow) setelah prototype disetujui.
4. **Data Modeling** — menentukan struktur data apa saja yang dibutuhkan dan perlu ditampilkan di tiap layar/modul.
5. **Development** — membangun backend, database, dan aplikasi Android (detail teknis di bagian 3–5).
6. **Deployment & Rilis**:
   - Upload aplikasi ke **Google Play Store**
   - Upload backend ke **hosting server**
   - Konfigurasi **DNS domain**
7. **Team Building & Leadership**:
   - Merekrut (hire) tim programmer tambahan untuk membantu pengerjaan **backoffice/admin panel**, **aplikasi Android**, dan **REST API**
   - Merancang alur kerja aplikasi (flow/spesifikasi) sebagai acuan kerja tim bantuan tersebut

Peran ini mencerminkan tanggung jawab **end-to-end**: mulai dari client discovery & product design, solo full-stack development, hingga deployment, infrastruktur, dan manajemen tim teknis.

---

## 3. Backend & REST API (`RentonBachkEnd-main`)

**Framework:** CodeIgniter 3 (PHP), dengan struktur *modular* (HMVC via third-party `MX`) yang memisahkan tiga area aplikasi ke dalam module tersendiri:

### a. Modul `api` — REST API untuk aplikasi mobile
Controller: `Basic`, `Customer`, `CustomerRent`, `Partner`, `PartnerRent`, `PartnerReward`, `RentVehicle`, `Chat`, `News`, `Rest_server`, serta manajemen API key (`api/Key`).
- Otentikasi berbasis **API key** menggunakan `REST_Controller` + `MY_Api` custom library
- Autentikasi user memakai library **Ion_auth**
- Push notification via **Firebase Cloud Messaging** (`Fcm.php`)

### b. Modul `admin` — Backoffice/Admin Panel
Controller: `Dashboard`, `Customer`, `Partner`, `Agent`, `RentVehicle`, `PartnerReward`, `Voucher`, `News`, `Report`, `Config`.
- Tema **AdminLTE** untuk UI dashboard
- Fitur laporan berbasis PDF menggunakan **TCPDF** (`MYPDF.php`)
- Manajemen master data, transaksi, voucher, reward, dan berita

### c. Modul `agent` — Portal khusus Agent
Controller: `Dashboard`, `Partner`, `Config`, `Agent`.
- Agent dapat memantau partner yang direkrut, komisi, riwayat transaksi, permintaan withdraw, dan konfigurasi rekening bank sendiri

### d. Lain-lain
- Dukungan **multi-bahasa** (30+ folder bahasa)
- Helper pengolahan gambar (`image_manipulation_helper.php`)
- Integrasi Firebase untuk web push (`firebase-config.js`, `firebase-messaging-sw.js`)

---

## 4. Database

3 file dump SQL disertakan (per Desember 2021): dataset produksi, dataset demo, dan dataset seed/startup. Total ±65 tabel, terbagi dalam domain berikut:

- **Wilayah/master data:** `provinces`, `regencies`, `districts`, `villages`, `vehicle_type`, `vehicle_model`, `brand`, `color`, `fuel`, `transmition_type`, `driven_type`, `functional_type`, `feature`
- **Aktor:** `customers` (+file, location), `partners` (+file, config, features), `agents` (+file, bank, commission, balance, withdraw)
- **Transaksi inti:** `rent_vehicles_item` (+images), `transaction_rent_vehicle` (+status, timeline), `transaction_repair_vehicle`, `transaction_point`
- **Keuangan/wallet:** `accounts_balance`, `accounts_bank`, `company_bank`, `customer_topup`, `customer_withdraw`, `agent_withdraw`, `history_agent_transaction`
- **Loyalty/marketing:** `vouchers`, `partner_rewards`, `reward_type`, `reward_scope`, `promote_rent_vehicle`, `news`
- **Engagement:** `chatroom`, `chat_message`, `notification`, `review_customer`, `review_vehicle`
- **Sistem:** `config`, `keys` (API key), `login_attempts`, `logs`

Struktur ini menunjukkan perancangan skema yang cukup matang untuk mendukung marketplace multi-pihak dengan sistem wallet, komisi bertingkat, dan program loyalti.

---

## 5. Aplikasi Android (`Renton-App-master`)

- **Nama aplikasi:** RentOn — package `com.rentone.user`
- **Bahasa:** Java — `minSdkVersion 21`, `targetSdkVersion 28`, versi rilis `1.5` (versionCode 7)
- **Arsitektur modul:** dipisah per role dalam satu app —
  - **Customer flow:** cari/filter/urutkan kendaraan, pemilihan lokasi & tanggal, checkout, riwayat transaksi, review
  - **Partner flow:** kelola daftar kendaraan, promosikan kendaraan, kelola transaksi masuk, review customer, pengaturan profil/perusahaan
  - **Fitur bersama:** Login & Registrasi multi-step (customer maupun partner), Home dengan rekomendasi, News, Live Chat (chatroom), Akun (rekening bank, topup, withdraw, tukar poin, riwayat saldo/poin)

**Teknologi/pustaka utama:**
- **Retrofit2 + OkHttp** — komunikasi REST API ke backend
- **Realm** — database lokal on-device
- **Firebase Analytics & Cloud Messaging** — analitik & push notification
- **Google Maps, Places, Location Services** — fitur berbasis lokasi (pencarian mitra/kendaraan terdekat)
- **Glide** — image loading, **Dexter** — runtime permission handling
- Modul terpisah `checkview` (custom library/UI component)

**Perizinan aplikasi:** lokasi (fine/coarse/background), kamera, storage, internet — sesuai kebutuhan marketplace berbasis lokasi dengan verifikasi foto (dokumen/kendaraan).

---

## 6. Daftar Lengkap Teknologi, Library & Dependency

### a. Backend — PHP / CodeIgniter (`RentonBachkEnd-main`)

**Core**
- CodeIgniter 3 (PHP)
- `MX` (Modular Extensions/HMVC) — struktur modular untuk memisahkan modul `admin`, `agent`, `api`

**Library internal/terintegrasi** (`application/libraries`)
- `REST_Controller.php` — base class REST API (Phil Sturgeon style)
- `MY_Api.php` — wrapper custom di atas REST_Controller (manajemen API key)
- `Ion_auth.php` — library autentikasi user (login, hash password, group/role)
- `Fcm.php` — integrasi Firebase Cloud Messaging (push notification)
- `MYPDF.php` — wrapper TCPDF untuk generate laporan PDF
- `Format.php` — helper formatting custom

**Third-party** (`application/third_party`)
- **TCPDF 6.3.5** — PDF generation
- **MX** (Modular Extensions HMVC)

**Frontend Admin Panel** (`assets/backend/AdminLTE`)
- **AdminLTE** — template dashboard admin
- **Bootstrap** — CSS framework
- **jQuery** — JS library
- **Font Awesome**, **Ionicons** — icon set
- **Chart.js** — grafik/chart di dashboard
- **CKEditor** — rich text editor (untuk konten News dsb.)
- **DataTables** + **DataTables Bootstrap** — tabel data interaktif
- **Select2** — dropdown/select yang diperkaya
- **bootstrap-datepicker**, **bootstrap-daterangepicker**, **bootstrap-timepicker**, **bootstrap-colorpicker** — input picker

**Layanan eksternal**
- **Firebase** — Cloud Messaging (push notification mobile) & Web Push (`firebase-config.js`, `firebase-messaging-sw.js`)
- **MySQL** — database

### b. Aplikasi Android (`Renton-App-master`)

**Build tooling**
- Android Gradle Plugin `3.6.0`
- `io.realm:realm-gradle-plugin:6.0.0`
- `com.google.gms:google-services:4.3.2`
- compileSdk/targetSdk `28`, minSdk `21` (module `checkview` minSdk `15`)

**AndroidX / Jetpack**
- `androidx.appcompat:appcompat:1.1.0`
- `androidx.constraintlayout:constraintlayout:1.1.3`
- `androidx.legacy:legacy-support-v4:1.0.0`
- `androidx.navigation:navigation-fragment:2.0.0` & `navigation-ui:2.0.0`
- `androidx.lifecycle:lifecycle-extensions:2.1.0`
- `androidx.cardview:cardview:1.0.0`
- `androidx.gridlayout:gridlayout:1.0.0-rc01`
- `com.google.android.material:material:1.0.0`
- `com.android.support:recyclerview-v7:28.0.0` (legacy support library, belum full-migrasi AndroidX)
- `com.android.support:appcompat-v7:27.1.1` (dipakai modul `checkview`)

**Database lokal**
- **Realm** (`io.realm`) — on-device database

**Networking / API**
- **Retrofit2** `2.6.2` + **converter-gson** `2.6.2`
- **OkHttp logging-interceptor** `3.12.1`

**Lokasi & Peta**
- `com.google.android.gms:play-services-maps:17.0.0`
- `com.google.android.gms:play-services-location:17.0.0`
- `com.google.android.libraries.places:places:2.0.0` (Places API)
- `com.google.maps.android:android-maps-utils:0.4`

**Firebase**
- `com.google.firebase:firebase-analytics:17.2.1`
- `com.google.firebase:firebase-messaging:20.1.0`
- `com.firebase:firebase-client-android:2.3.1` (legacy Firebase Realtime Database client)

**UI/UX & Media**
- **Glide** `4.10.0` (+ annotation processor) — image loading
- **ImagePicker** (`com.github.nguyenhoanglam:ImagePicker:1.3.3`) — pemilihan gambar
- **Shimmer** (`com.facebook.shimmer:shimmer:0.5.0`) — efek loading skeleton
- **CircleImageView** (`de.hdodenhof:circleimageview:2.2.0`)
- **CrystalRangeSeekbar** `1.1.3` — filter rentang harga
- **CarouselView** (`com.synnapps:carouselview:0.1.5`) — slider gambar
- **ZoomLayout** (`com.otaliastudios:zoomlayout:1.7.0`) — zoom galeri foto
- **RangePicker** (`com.savvi.datepicker:rangepicker:1.3.0`) — date range picker

**Perizinan runtime**
- **Dexter** (`com.karumi:dexter:6.0.0`) — permission handling

**Modul internal**
- `checkview` — custom Android library module (proyek sendiri, dependency terpisah)

**Testing**
- `junit:junit:4.12`
- `androidx.test.ext:junit:1.1.0`
- `androidx.test.espresso:espresso-core:3.1.1`

---

## 7. Deployment & Infrastruktur

- **Distribusi mobile:** upload & rilis ke **Google Play Store**
- **Hosting backend:** upload aplikasi backend (CodeIgniter) ke **hosting server**
- **Domain:** konfigurasi **DNS domain** untuk mengarahkan ke server produksi
- **Push notification:** konfigurasi Firebase project untuk FCM (mobile) dan web push

---

## 8. Manajemen Tim

- Merekrut tim programmer tambahan untuk membagi beban kerja pada 3 area: **admin backoffice**, **aplikasi Android**, dan **REST API**
- Berperan sebagai **perancang alur kerja/flow aplikasi** yang menjadi acuan implementasi bagi tim bantuan — menunjukkan kapasitas sebagai **technical lead**, bukan hanya individual contributor

---

## 9. Ringkasan Kompetensi yang Terdemonstrasikan

- **Product discovery & client management** — diskusi kebutuhan langsung dengan owner/stakeholder
- **Prototyping & system design** — flowchart, data modeling, UX requirement
- **Full-stack development** — PHP/CodeIgniter (backend + admin panel + REST API), MySQL, Android native (Java)
- **Integrasi pihak ketiga** — Firebase (Auth/FCM/Analytics), Google Maps/Places, payment/wallet system custom
- **DevOps ringan** — deployment ke Play Store, hosting server, konfigurasi DNS
- **Leadership** — rekrutmen & pengarahan tim developer, penyusunan spesifikasi kerja untuk tim

---

## 10. Bagian yang Bisa Ditulis di CV

Bagian ini siap dipakai langsung di CV/portofolio — tinggal salin dan sesuaikan periode kerja.

### Judul Posisi (pilih salah satu sesuai penempatan)
`Full-Stack Developer & Project Lead` — atau — `Founder / Technical Lead` (jika ditulis sebagai proyek pribadi/startup)

### Deskripsi Singkat (1 baris, untuk ringkasan profil)
> Memimpin pengembangan platform marketplace sewa kendaraan end-to-end, dari riset kebutuhan hingga rilis produksi dan pembentukan tim developer.

### Poin-Poin Pengalaman (bullet points siap pakai)

- Memimpin pengembangan **RentOn**, platform marketplace sewa kendaraan yang menghubungkan customer, mitra (partner), dan agen, mulai dari tahap diskusi kebutuhan dengan owner hingga rilis produksi.
- Merancang **prototype**, **flowchart alur sistem**, dan **struktur data** sebagai dasar pengembangan tiga sistem: REST API, admin backoffice, dan aplikasi mobile Android.
- Membangun **REST API dan admin backoffice** menggunakan PHP (CodeIgniter 3) dengan sistem otentikasi berbasis API key, manajemen transaksi, keuangan/wallet (topup, withdraw, komisi agen), serta modul reward & voucher.
- Merancang dan mengimplementasikan **skema database MySQL** (±65 tabel) untuk mendukung marketplace multi-pihak dengan sistem wallet, komisi bertingkat, dan program loyalti pelanggan.
- Mengembangkan **aplikasi Android native (Java)** dengan dua alur pengguna (customer & partner) mencakup pencarian & sewa kendaraan, chat real-time, sistem saldo/poin, serta integrasi Google Maps, Firebase, dan Retrofit.
- Melakukan **deployment aplikasi ke Google Play Store**, hosting backend ke server produksi, dan konfigurasi **DNS domain**.
- **Merekrut dan memimpin tim programmer** untuk mengerjakan admin panel, aplikasi Android, dan REST API, termasuk menyusun alur kerja/spesifikasi teknis sebagai acuan implementasi tim.

### Skills/Tech Stack (untuk bagian keahlian di CV)
`PHP (CodeIgniter 3)` · `MySQL` · `REST API Design` · `Android (Java)` · `Firebase (FCM, Analytics)` · `Google Maps/Places API` · `Retrofit/OkHttp` · `System Design & Flowcharting` · `Database Design` · `Team Leadership` · `Deployment (Play Store, Hosting, DNS)`
