# Audit Clean Architecture - Fase 2: Purifikasi Layer Domain

Status: **IN PROGRESS**

## 🛑 Architecture Violations Found

### 1. Framework Dependencies (Leakage)
- **`android.net.Uri`**: Found in `CustomerRepository`, `PartnerRepository`, `PartnerRentVehicleRepository`.
- **`okhttp3.RequestBody` / `MultipartBody`**: Found in `CustomerRepository`, `PartnerRepository`, `RegisterPartnerUseCase`, etc.
- **`retrofit2.*`**: Some interfaces might be leaking these indirectly if not careful.

### 2. DTO Leakage (Domain knowing about Data schema)
- **`GetLoginResponse`**: Leaked into `CustomerRepository`, `LoginUseCase`.
- **`CheckEmailResponse`**, **`CheckPhoneResponse`**, **`CheckAgentResponse`**: Leaked into `LookupRepository`.
- **`PartnerListVehicleResponse`**, **`UploadImageResponse`**: Leaked into `PartnerRentVehicleRepository`.
- **`ListVehicleResponse`**, **`ListVehicleReviewResponse`**, **`CheckVoucherResponse`**: Leaked into `VehicleRepository`.

### 3. ISP Violations (Oversized Repositories)
- **`CustomerRepository`**: Handles Auth, Account, Finance, Banks, Topups, and Withdrawals. (Needs splitting).
- **`PartnerRepository`**: Handles Registration, Profile, and Feature Requests.
- **`PartnerRentVehicleRepository`**: Handles Vehicle CRUD, Promotion, and Transactions.

### 4. Command Objects Missing
- Use Cases are passing `Map<String, String>` or `RequestBody` instead of Domain-specific Command/Request objects.

---

## 🏗️ Refactor Plan

### Tahap 1: Pembersihan Model Domain
- [ ] Hapus `@Serializable` atau `@SerializedName` (jika ada yang terikat framework) dari domain model.
- [ ] Buat Domain Model murni untuk menggantikan DTO yang bocor (misal: `LoginResult`, `ValidationResult`).

### Tahap 2: Pemisahan Repository (ISP)
- [ ] Split `CustomerRepository` menjadi `AuthRepository`, `CustomerAccountRepository`, `CustomerFinanceRepository`.
- [ ] Split `PartnerRepository` sesuai tanggung jawabnya.

### Tahap 3: Implementasi Command Objects
- [ ] Buat `RegisterCommand`, `UploadImageCommand`, `LoginCommand`, dll.

### Tahap 4: Purifikasi Interface Repository & Use Case
- [ ] Hapus import `okhttp3`, `android.net.Uri`, dan `api.model` dari seluruh layer Domain.
- [ ] Update Repository Interface untuk menggunakan model domain dan command objects.
- [ ] Pindahkan logic mapping DTO -> Domain ke layer Data (RepositoryImpl).

### Tahap 5: Audit Akhir
- [ ] Pastikan 0 framework dependencies di `com.nusatim.sapiriku.domain`.
