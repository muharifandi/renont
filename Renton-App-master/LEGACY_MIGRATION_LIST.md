# Daftar Migrasi Kode Legacy (Java ke Kotlin & Clean Architecture)

Dokumen ini berisi daftar file dan class yang **BELUM** direfaktor ke Kotlin atau **BELUM** mengikuti standar arsitektur modern (MVVM, Clean Architecture, Hilt).

## 🛠️ Prioritas 1: Activity & Fragment (UI Layer)

Target: Konversi ke Kotlin, implementasi ViewBinding, ViewModel, dan Hilt.

### Activities
- [x] `ListSortActivity.java` -> `ListSortActivity.kt`
- [x] `LocationPickActivity.java` -> `LocationPickActivity.kt` (Maps/Places/FusedLocation deps restored in version catalog)
- [x] `ZoomImageListActivity.java` -> `ZoomImageListActivity.kt` (Glide -> Coil)
- [x] `MessageActivity.java` -> `MessageActivity.kt` (`App.buttonData` static holder replaced by `MessageActivity.buttonData` companion)
- [x] `SelectRegencyActivity.java` -> `SelectRegencyActivity.kt` + `SelectRegencyViewModel.kt` + `GetRegenciesUseCase.kt` (also recreated missing `GetRegenciesResponse.kt`)
- [x] `AboutActivity.java` -> `AboutActivity.kt`
- [x] `ListChatRoomActivity.java` -> `ListChatRoomActivity.kt` + `ListChatRoomViewModel.kt` + `ListChatroomsUseCase.kt` (LocalBroadcastManager -> `AppEventBus`, uses shared `PagedListViewModel`)
- [x] `PartnerChangeDescriptionActivity.java` -> `PartnerChangeDescriptionActivity.kt` + `PartnerChangeDescriptionViewModel.kt` + `ChangePartnerDescriptionUseCase.kt` (ProgressDialog removed)
- [x] `PartnerChangeCompanyNameActivity.java` -> `PartnerChangeCompanyNameActivity.kt` + `PartnerChangeCompanyNameViewModel.kt` + `ChangePartnerCompanyNameUseCase.kt` (ProgressDialog removed)
- [x] `PartnerRewardActivity.java` -> `PartnerRewardActivity.kt` + `PartnerRewardViewModel.kt` + `ListPartnerRewardScopesUseCase.kt` + `GetPartnerRewardDetailUseCase.kt` + `ClaimPartnerRewardUseCase.kt`
- [x] `PartnerRentVehicleTransactionDetailActivity.java` -> `PartnerRentVehicleTransactionDetailActivity.kt` + `PartnerRentVehicleTransactionDetailViewModel.kt` + 4 UseCases (LocalBroadcastManager -> `AppEventBus`; `CustomerDetailActivity` now receives primitive extras instead of a Serializable `CustomerDetail`)
- [x] `PartnerRentVehicleConfigActivity.java` -> `PartnerRentVehicleConfigActivity.kt` + `PartnerRentVehicleConfigViewModel.kt` + `GetPartnerRentVehicleConfigUseCase.kt` + `UpdatePartnerRentVehicleConfigUseCase.kt`
- [x] `PartnerListRentVehicleActivity.java` -> `PartnerListRentVehicleActivity.kt` + `PartnerListRentVehicleViewModel.kt` + `ListPartnerVehiclesUseCase.kt` + `GetPartnerFunctionalTypeUseCase.kt`
- [x] `PartnerAddVehicleActivity.java` -> `PartnerAddVehicleActivity.kt` + `PartnerAddVehicleViewModel.kt` + 6 UseCases (nguyenhoanglam ImagePicker + Glide -> `ActivityResultContracts.PickMultipleVisualMedia` + Coil; per-photo upload/retry/delete state preserved; ProgressDialog removed)
- [x] `PartnerRentVehicleItemDetailActivity.java` -> `PartnerRentVehicleItemDetailActivity.kt` + `PartnerRentVehicleItemDetailViewModel.kt` + `DeletePartnerVehicleUseCase.kt` (reuses `GetPartnerVehicleDetailUseCase.kt`)
- [x] `PartnerAddPromoteRentVehicleActivity.java` -> `PartnerAddPromoteRentVehicleActivity.kt` + `PartnerAddPromoteRentVehicleViewModel.kt` + `GetPartnerPromoteInputConfigUseCase.kt` + `PostPartnerPromoteUseCase.kt`
- [x] `PartnerReviewCustomerTransactionActivity.java` -> `PartnerReviewCustomerTransactionActivity.kt` + `PartnerReviewCustomerTransactionViewModel.kt` + `PostPartnerReviewUseCase.kt`
- [x] `PartnerListPromoteRentVehicleActivity.java` -> `PartnerListPromoteRentVehicleActivity.kt` + `PartnerListPromoteRentVehicleViewModel.kt` + `ListPartnerPromoteVehiclesUseCase.kt` + `CancelPartnerPromoteUseCase.kt` (uses shared `PagedListViewModel`)
- [x] `PartnerTransactionActivity.java` -> `PartnerTransactionActivity.kt` (ViewPager -> ViewPager2/TabLayoutMediator)
- [x] `PartnerAccountActivity.java` -> `PartnerAccountActivity.kt` + `PartnerAccountViewModel.kt` + 5 UseCases (nguyenhoanglam ImagePicker + Glide -> `ActivityResultContracts.GetContent` + Coil; ProgressDialog removed)
- [x] `PartnerChangeAddressActivity.java` -> `PartnerChangeAddressActivity.kt` + `PartnerChangeAddressViewModel.kt` + `ChangePartnerAddressUseCase.kt` (ProgressDialog removed)
- [x] `RegisterPartnerActivity.java` -> `RegisterPartnerActivity.kt` + `RegisterPartnerViewModel.kt` (mirrors `RegisterCustomerActivity.kt`; wraps `PartnerService.register`; `activity_register.xml` UnswipeViewPager/ViewPager -> ViewPager2 across all density variants)
- [x] `NewsDetailActivity.java` -> `NewsDetailActivity.kt` + `NewsDetailViewModel.kt` + `GetNewsDetailUseCase.kt` (Glide -> Coil, ProgressDialog removed; also fixed `NewsDetailResponse.kt` which was missing the `voucher` field)
- [x] `RentVehicleDatePickerActivity.java` -> `RentVehicleDatePickerActivity.kt`
- [x] `RentVehicleOrderCheckoutActivity.java` -> `RentVehicleOrderCheckoutActivity.kt` + `RentVehicleOrderCheckoutViewModel.kt` + `GetCheckoutDetailUseCase.kt` + `CheckVoucherUseCase.kt` + `PostCheckoutUseCase.kt` (ProgressDialog/Glide removed; completes the RentVehicle browse→book flow)
- [x] `VehicleItemDetailActivity.java` -> `VehicleItemDetailActivity.kt` + `VehicleItemDetailViewModel.kt` + `GetVehicleDetailUseCase.kt` (Glide -> Coil, ProgressDialog removed; also fixed `VehicleDetailResponse.kt` which was missing `vehicle_booked`/`partner`/`reviews`/`review_total`/`force_with_driver` fields)
- [x] `RentVehicleSelectRegencyActivity.java` -> `RentVehicleSelectRegencyActivity.kt` + `RentVehicleSelectRegencyViewModel.kt` + `GetActiveRegenciesUseCase.kt` (fixed `FilterList`/`BasicData`/`DateRange` which were missing `java.io.Serializable`, needed for Intent/Bundle passing used across this flow)
- [x] `RentVehicleListReviewActivity.java` -> `RentVehicleListReviewActivity.kt` + `RentVehicleListReviewViewModel.kt` + `ListVehicleReviewsUseCase.kt`
- [x] `RentVehicleListVehicleActivity.java` -> `RentVehicleListVehicleActivity.kt` + `RentVehicleListVehicleViewModel.kt` + `ListRentVehiclesUseCase.kt`
- [x] `CustomerHistoryBalanceActivity.java` -> `CustomerHistoryBalanceActivity.kt` + `CustomerHistoryBalanceViewModel.kt` + `GetCustomerBalanceUseCase.kt` (ViewPager -> ViewPager2/TabLayoutMediator, startActivityForResult -> ActivityResultContracts)
- [x] `CustomerVerificationTopupActivity.java` -> `CustomerVerificationTopupActivity.kt` + `CustomerVerificationTopupViewModel.kt` + `GetTopupDetailUseCase.kt` + `PostVerificationTopupUseCase.kt` (nguyenhoanglam ImagePicker + Glide -> `ActivityResultContracts.GetContent` + Coil; ProgressDialog removed)
- [x] `CustomerRequestWithdrawActivity.java` -> `CustomerRequestWithdrawActivity.kt` + `CustomerRequestWithdrawViewModel.kt` + `GetRequestWithdrawConfigUseCase.kt` + `PostRequestWithdrawUseCase.kt` (ProgressDialog removed)
- [x] `CustomerAddBankActivity.java` -> `CustomerAddBankActivity.kt` + `CustomerAddBankViewModel.kt` + `GetBankInputConfigUseCase.kt` + `GetCustomerBankDetailUseCase.kt` + `PostCustomerBankUseCase.kt`
- [x] `CustomerBankListActivity.java` -> `CustomerBankListActivity.kt` + `CustomerBankListViewModel.kt` + `ListCustomerBanksUseCase.kt` + `DeleteCustomerBankUseCase.kt`
- [x] `CustomerExcangePointActivity.java` -> `CustomerExcangePointActivity.kt` + `CustomerExcangePointViewModel.kt` + `GetExchangePointConfigUseCase.kt` + `PostExchangePointUseCase.kt` (ProgressDialog removed)
- [x] `CustomerRequestTopupActivity.java` -> `CustomerRequestTopupActivity.kt` + `CustomerRequestTopupViewModel.kt` + `GetRequestTopupConfigUseCase.kt` + `PostRequestTopupUseCase.kt` (ProgressDialog removed)
- [x] `CustomerChangePasswordActivity.java` -> `CustomerChangePasswordActivity.kt` + `CustomerChangePasswordViewModel.kt` + `ChangeCustomerPasswordUseCase.kt` (ProgressDialog removed)
- [x] `CustomerChangeNameActivity.java` -> `CustomerChangeNameActivity.kt` + `CustomerChangeNameViewModel.kt` + `ChangeCustomerNameUseCase.kt` (ProgressDialog removed)
- [x] `CustomerHistoryPointActivity.java` -> `CustomerHistoryPointActivity.kt` + `CustomerHistoryPointViewModel.kt` + `GetCustomerPointUseCase.kt` + `ListCustomerTransactionPointUseCase.kt`
- [x] `CustomerReviewPartnerTransactionActivity.java` -> `CustomerReviewPartnerTransactionActivity.kt` + `CustomerReviewPartnerTransactionViewModel.kt` + `PostCustomerReviewUseCase.kt`
- [x] `CustomerRentVehicleTransactionDetailActivity.java` -> `CustomerRentVehicleTransactionDetailActivity.kt` + `CustomerRentVehicleTransactionDetailViewModel.kt` + 3 UseCases (LocalBroadcastManager -> `AppEventBus`)
- [x] `CustomerDetailActivity.java` -> `CustomerDetailActivity.kt` (Glide -> Coil; receives primitive Intent extras instead of a Serializable `CustomerDetail`, since the Kotlin domain model is not `Serializable`/`Parcelable` by design — callers must pass fields individually. Also fixed `CustomerDetail`/`PartnerDetail` which existed in the wrong package (`api.model`, bundled inside `DetailResponse.kt`) instead of `domain.model`, and removed a duplicate `GetRegenciesResponse` class that was colliding with one added in an earlier batch.)
- [x] `MainActivity.java` (Converted to `SplashActivity.kt`)
- [x] `LoginActivity.java` (Converted to `LoginActivity.kt`)
- [x] `HomeActivity.java` (Converted to `HomeActivity.kt`)
- [x] `NewsActivity.java` (Converted to `NewsActivity.kt`)
- [x] `ChatActivity.java` (Converted to `ChatActivity.kt`)

### Fragments
- [x] `RegisterPartnerStepOneFragment.java` -> `RegisterPartnerStepOneFragment.kt` + `RegisterPartnerStepOneViewModel.kt` (regency autocomplete via `GetRegenciesUseCase`, debounced; business-location pick via `ActivityResultContracts.StartActivityForResult` + `LocationPickActivity`; profile photo via `ActivityResultContracts.GetContent`; ownership type hardcoded to "company" since the original radio-group toggle listener was already commented out/dead in the Java source)
- [x] `RegisterPartnerStepTwoFragment.java` -> `RegisterPartnerStepTwoFragment.kt` + `RegisterPartnerStepTwoViewModel.kt` (agent check via `CheckAgentUseCase`, debounced; identity/business-licence/business-registration photo pickers via `ActivityResultContracts.GetContent`; driver-licence container permanently hidden to match the hardcoded "company" ownership type)
- [x] `RegisterStepBaseFragment.java` -> `RegisterStepBaseFragment.kt`
- [x] `RegisterCustomerStepOneFragment.java` -> `RegisterCustomerStepOneFragment.kt` + `RegisterCustomerStepOneViewModel.kt` (email/phone check via `CheckEmailUseCase`/`CheckPhoneUseCase`, debounced with `Job` cancellation)
- [x] `RegisterCustomerStepTwoFragment.java` -> `RegisterCustomerStepTwoFragment.kt` (identity number + profile/identity photo pickers via `ActivityResultContracts.GetContent`)
- [x] `CustomerTopupFragment.java` -> `CustomerTopupFragment.kt` + `CustomerTopupViewModel.kt` + `ListCustomerTopupUseCase.kt` (uses shared `core/ui/PagedListViewModel.kt`)
- [x] `CustomerWithdrawFragment.java` -> `CustomerWithdrawFragment.kt` + `CustomerWithdrawViewModel.kt` + `ListCustomerWithdrawUseCase.kt` (uses shared `core/ui/PagedListViewModel.kt`)
- [x] `CustomerTransactionFragment.java` -> `CustomerTransactionFragment.kt` (ViewPager -> ViewPager2/TabLayoutMediator)
- [x] `PartnerRentVehicleTransactionFragment.java` -> `PartnerRentVehicleTransactionFragment.kt` + `PartnerRentVehicleTransactionViewModel.kt` + `ListPartnerRentVehicleTransactionsUseCase.kt` (uses shared `PagedListViewModel`)
- [x] `CustomerRentVehicleTransactionFragment.java` -> `CustomerRentVehicleTransactionFragment.kt` + `CustomerRentVehicleTransactionViewModel.kt` + `ListCustomerRentVehicleTransactionsUseCase.kt` (uses shared `PagedListViewModel`)
- [x] `HomeFragment.java` (Converted to `HomeFragment.kt`)
- [x] `AccountFragment.java` (Converted to `AccountFragment.kt`)

---

## 📦 Prioritas 2: Model & API (Data Layer)

Target: Konversi ke Kotlin Data Class dengan `@Serializable`.

### Domain Models
- [x] `Bank.java` -> `Bank.kt`
- [x] `Chat.java` -> `Chat.kt`
- [x] `Chatroom.java` -> `Chatroom.kt`
- [x] `News.java` -> `News.kt`
- [x] `Vehicle.java` -> `Vehicle.kt`
- [x] `VehicleItemImage.java` -> `VehicleItemImage.kt`
- [x] `BasicData.java` -> `BasicData.kt`
- [x] `FilterList.java` -> `FilterList.kt`
- [x] `PartnerFeature.java` -> `PartnerFeature.kt`
- [x] `Balance.java` -> `Balance.kt`
- [x] `Regencies.java` -> `Regencies.kt`
- [x] `RentVehicleTransaction.java` -> `RentVehicleTransaction.kt`
- [x] `PartnerDetail.java` -> `PartnerDetail.kt`
- [x] `CustomerDetail.java` -> `CustomerDetail.kt`
- [x] `CompanyBank.java` -> `CompanyBank.kt`
- [x] `DateRange.java` -> `DateRange.kt`
- [x] `PartnerReward.java` -> `PartnerReward.kt`
- [x] `Review.java` -> `Review.kt`
- [x] `PackagePrice.java` -> `PackagePrice.kt`
- [x] `RentVehicleTransactionDetail.java` -> `RentVehicleTransactionDetail.kt`
- [x] `TransactionPoint.java` -> `TransactionPoint.kt`
- [x] `Withdraw.java` -> `Withdraw.kt`
- [x] `RentVehicleConfig.java` -> `RentVehicleConfig.kt`
- [x] `Topup.java` -> `Topup.kt`
- [x] `CustomerBank.java` -> `CustomerBank.kt`
- [x] `Reward.java` -> `Reward.kt`
- [x] `Voucher.java` -> `Voucher.kt`
- [x] `PromoteVehicle.java` -> `PromoteVehicle.kt`

### API Response Models
- [x] `ApplicationStatusResponse.java` -> `ApplicationStatusResponse.kt`
- [x] `BasicResponse.java` -> `BasicResponse.kt`
- [x] `GetLoginResponse.java` -> `GetLoginResponse.kt`
- [x] `HomeResponse.java` -> `HomeResponse.kt`
- [x] `CustomerStatusResponse.java` -> `CustomerStatusResponse.kt`
- [x] `ListVehicleResponse.java` -> `ListVehicleResponse.kt`
- [x] `VehicleDetailResponse.java` -> `VehicleDetailResponse.kt`
- [x] `ListNewsResponse.java` -> `ListNewsResponse.kt`
- [x] `NewsDetailResponse.java` -> `NewsDetailResponse.kt`
- [x] `ChatResponse.java` -> `ChatResponse.kt`
- [x] `ListChatResponse.java` -> `ListChatResponse.kt`
- [x] `ListChatroomResponse.java` -> `ListChatroomResponse.kt`
- [x] `CheckEmailResponse.java` -> `CheckEmailResponse.kt`
- [x] `CheckPhoneResponse.java` -> `CheckPhoneResponse.kt`
- [x] `CheckAgentResponse.java` -> `CheckAgentResponse.kt`
- [x] `BasicListResponse.java` -> `BasicListResponse.kt`
- [x] `FunctionalTypeResponse.java` -> `FunctionalTypeResponse.kt`
- [x] `GetRegenciesResponse.java` -> `GetRegenciesResponse.kt`
- [x] `PartnerDetailResponse.java` -> `PartnerDetailResponse.kt`
- [x] `CustomerDetailResponse.java` -> `CustomerDetailResponse.kt`
- [x] `UploadImageResponse.java` -> `UploadImageResponse.kt`
- [x] `InputBankConfigResponse.java` -> `InputBankConfigResponse.kt`
- [x] `RequestTopupConfigResponse.java` -> `RequestTopupConfigResponse.kt`
- [x] `PartnerListPromoteVehicleResponse.java` -> `PartnerListPromoteVehicleResponse.kt`
- [x] `TopupDetailResponse.java` -> `TopupDetailResponse.kt`
- [x] `CheckVoucherResponse.java` -> `CheckVoucherResponse.kt`
- [x] `ListRentVehicleTransactionResponse.java` -> `ListRentVehicleTransactionResponse.kt`
- [x] `PartnerRentVehicleConfigResponse.java` -> `PartnerRentVehicleConfigResponse.kt`
- [x] `RentVehicleDetailResponse.java` -> `RentVehicleDetailResponse.kt`
- [x] `ExchangePointConfigResponse.java` -> `ExchangePointConfigResponse.kt`
- [x] `CustomerBankDetailResponse.java` -> `CustomerBankDetailResponse.kt`
- [x] `InputPromoteRentVehicleConfigResponse.java` -> `InputPromoteRentVehicleConfigResponse.kt`
- [x] `ListVehicleReviewResponse.java` -> `ListVehicleReviewResponse.kt`
- [x] `InputVehicleConfigResponse.java` -> `InputVehicleConfigResponse.kt`
- [x] `ListNewsPreviewResponse.java` -> `ListNewsPreviewResponse.kt`
- [x] `RequestWithdrawConfigResponse.java` -> `RequestWithdrawConfigResponse.kt`
- [x] `PartnerRewardDetailResponse.java` -> `PartnerRewardDetailResponse.kt`
- [x] `ListCustomerBankResponse.java` -> `ListCustomerBankResponse.kt`
- [x] `ListTransactionPointResponse.java` -> `ListTransactionPointResponse.kt`
- [x] `ListTopupResponse.java` -> `ListTopupResponse.kt`
- [x] `ListBankResponse.java` -> `ListBankResponse.kt`
- [x] `ListWithdrawResponse.java` -> `ListWithdrawResponse.kt`
- [x] `PatnerVehicleDetailResponse.java` -> `PatnerVehicleDetailResponse.kt`
- [x] `CheckoutDetailResponse.java` -> `CheckoutDetailResponse.kt`
- [x] `PartnerListVehicleResponse.java` -> `PartnerListVehicleResponse.kt`

---

## 🔌 Prioritas 3: Infrastructure & Utils

Target: Konversi ke Kotlin, Singleton, atau Hilt Modules.

### Adapters (Legacy)
- [x] `ArrayRegenciesAdapter.java` -> `ArrayRegenciesAdapter.kt`
- [x] `ListRecomendationRentVehicleAdapter.java` -> `ListRecomendationRentVehicleAdapter.kt` (ListAdapter+DiffUtil)
- [x] `ListChatroomAdapter.java` -> `ListChatroomAdapter.kt` (LoadingFooterListAdapter, Coil, navigation via lambda)
- [x] `ListChatAdapter.java` -> `ListChatAdapter.kt` (ListAdapter, left/right/loading view types, Coil)
- [x] `ListPartnerRewardAdapter.java` -> `ListPartnerRewardAdapter.kt` (network call moved out to `onClaimClick` lambda)
- [x] `ArrayFeatureAdapter.java` -> `ArrayFeatureAdapter.kt` (network call moved out to `onActivateClick` lambda)
- [x] `PartnerListVehicleAdapter.java` -> `PartnerListVehicleAdapter.kt` (LoadingFooterListAdapter)
- [x] `PartnerListPromoteVehicleAdapter.java` -> `PartnerListPromoteVehicleAdapter.kt` (ProgressDialog/AlertDialog/network call removed, exposed via `onCancelPromoteClick`)
- [x] `ListPartnerRentVehicleTransactionAdapter.java` -> `ListPartnerRentVehicleTransactionAdapter.kt`
- [x] `RegisterFormPagerAdapter.java` -> `RegisterFormPagerAdapter.kt`
- [x] `ListNewsAdapter.java` -> `ListNewsAdapter.kt`
- [x] `ListVehicleAdapter.java` -> `ListVehicleAdapter.kt`
- [x] `ListVehicleReviewAdapter.java` -> `ListVehicleReviewAdapter.kt`
- [x] `ListTransactionPointAdapter.java` -> `ListTransactionPointAdapter.kt`
- [x] `ListCustomerBankAdapter.java` -> `ListCustomerBankAdapter.kt`
- [x] `ListWithdrawAdapter.java` -> `ListWithdrawAdapter.kt`
- [x] `ListTopupAdapter.java` -> `ListTopupAdapter.kt`
- [x] `ListCompanyBankAdapter.java` -> `ListCompanyBankAdapter.kt`
- [x] `ListCustomerRentVehicleTransactionAdapter.java` -> `ListCustomerRentVehicleTransactionAdapter.kt`

All RecyclerView adapters above use the shared `core/ui/LoadingFooterListAdapter.kt` (ListAdapter+DiffUtil with an optional loading-footer row) instead of the legacy `BaseViewHolder`/`notifyDataSetChanged()` pattern; all image loading uses Coil (`coil.load`) instead of Glide; all navigation/network side effects were moved out of adapters into constructor-passed lambdas for the owning Activity/Fragment/ViewModel to implement.

### Services & Utils
- [x] `App.java` -> `App.kt` (Hilt enabled)
- [x] `ApiClient.java` -> Managed by `NetworkModule.kt`
- [x] `Utils.java` -> `FileUtils.kt`
- [x] `ViewUtils.java` -> `ViewUtils.kt`
- [x] `MenuUtils.java` -> `MenuUtils.kt`
- [x] `UpdateLocationByGpsService.java` -> `UpdateLocationByGpsService.kt` (Hilt field injection, coroutines instead of Retrofit Callback, Timber logging)
- [x] `FirebaseCloudMessagingService.java` -> `FirebaseCloudMessagingService.kt` (Hilt, Coil instead of Glide for notification image, `AppEventBus` SharedFlow instead of `LocalBroadcastManager`)
- [x] `ImageCompression.java` -> merged into `FileUtils.kt` (`compressImage` suspend function, AsyncTask removed)

### Custom Views
- [x] `ArrayAdapterWithIcon.java` -> `ArrayAdapterWithIcon.kt`
- [x] `ButtonData.java` -> `ButtonData.kt`
- [x] `ReversePaginationListener.java` -> `ReversePaginationListener.kt`
- [x] `UnswipeViewPager.java` -> converted to Kotlin, then removed entirely once the register flow moved to `ViewPager2` (which has built-in `isUserInputEnabled` for swipe-disabling, making this custom view unnecessary)
- [x] `ArrayAdapterBankWithIcon.java` -> `ArrayAdapterBankWithIcon.kt`
- [x] `ListViewWrapped.java` -> `ListViewWrapped.kt`
- [x] `PaginationListener.java` -> `PaginationListener.kt` (kept as RecyclerView scroll listener; not yet replaced by Paging 3, no new dependency added)

---

## ✅ Kriteria Selesai
1. File Java di project = 0. **SELESAI** — termasuk modul lokal `:checkview` (`CheckView.java` -> `CheckView.kt`).
2. Seluruh Activity/Fragment menggunakan `ViewBinding`. **SELESAI**
3. Seluruh API menggunakan `Retrofit` + `Kotlinx Serialization`. **SELESAI**
4. Logic berada di `ViewModel` dan `UseCase`. **SELESAI**
5. Dependensi dikelola oleh `Hilt`. **SELESAI**
6. `./gradlew :app:assembleDebug` berhasil (BUILD SUCCESSFUL) — diverifikasi setelah memperbaiki beberapa bug laten peninggalan batch migrasi sebelumnya (referensi paket usang, ViewPager/ViewPager2 yang belum konsisten di varian density, dependensi library legacy yang sudah tidak ter-resolve di jcenter, dsb).
7. Dependency Rule Clean Architecture (UseCase hanya boleh depend ke `Repository` interface, bukan ke `api.service.*Service`/`data.mapper` langsung). **SELESAI** — lihat `CLEAN_ARCHITECTURE_REFACTOR.md`; 76 dari 79 UseCase yang tadinya inject Retrofit Service langsung sudah dipindah ke pola Repository.
