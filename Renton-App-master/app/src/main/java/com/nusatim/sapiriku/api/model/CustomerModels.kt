package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class RegisterCustomerData(
    @SerialName("account_id") val accountId: Int,
    @SerialName("key") val key: String
)

@Serializable
data class LoginData(
    @SerialName("account_id") val accountId: Int,
    @SerialName("key") val key: String
)

@Serializable
data class LoginRequest(
    @SerialName("email") val email: String,
    @SerialName("password") val password: String
)

@Serializable
data class CustomerDetail(
    @SerialName("id") val id: Int,
    @SerialName("email") val email: String? = null,
    @SerialName("first_name") val firstName: String? = null,
    @SerialName("last_name") val lastName: String? = null,
    @SerialName("member_since") val memberSince: String? = null,
    @SerialName("img_profile") val imgProfile: String? = null,
    @SerialName("referal_id") val referalId: Int? = null
)

@Serializable
data class CustomerDetailData(
    @SerialName("customer") val customer: CustomerDetail? = null,
    @SerialName("balance") val balance: BalanceData? = null,
    @SerialName("bank_total") val bankTotal: Int = 0
)

/** `accounts_bank.*` + joined bank name/code/icon -- customer's own saved bank accounts. */
@Serializable
data class CustomerBank(
    @SerialName("id") val id: Int,
    @SerialName("account_id") val accountId: Int? = null,
    @SerialName("bank_id") val bankId: Int? = null,
    @SerialName("bank_number") val bankNumber: String? = null,
    @SerialName("name") val name: String? = null,
    @SerialName("bank_name") val bankName: String? = null,
    @SerialName("code") val code: String? = null,
    @SerialName("icon") val icon: String? = null
)

@Serializable
data class CustomerBanksData(
    @SerialName("banks") val banks: List<CustomerBank> = emptyList()
)

@Serializable
data class CustomerBankDetailData(
    @SerialName("bank") val bank: CustomerBank? = null,
    /** Present instead of [bank] when the id path param is omitted -- master list of bank institutions. */
    @SerialName("banks") val bankOptions: List<BankOption>? = null
)

/** `bank` table row -- master list of bank institutions (for dropdowns), not a saved account. */
@Serializable
data class BankOption(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("code") val code: String? = null,
    @SerialName("icon") val icon: String? = null
)

@Serializable
data class SaveBankRequest(
    @SerialName("bank_id") val bankId: Int,
    @SerialName("name") val name: String,
    @SerialName("bank_number") val bankNumber: String,
    /** Omit to create a new bank account; include to update an existing one. */
    @SerialName("id") val id: Int? = null
)

@Serializable
data class SavedBankIdData(
    @SerialName("id") val id: Int
)

@Serializable
data class ProfileImageData(
    @SerialName("img_profile") val imgProfile: String
)

@Serializable
data class UpdateNameRequest(
    @SerialName("first_name") val firstName: String,
    @SerialName("last_name") val lastName: String
)

@Serializable
data class ChangePasswordRequest(
    @SerialName("old_password") val oldPassword: String,
    @SerialName("new_password") val newPassword: String
)

/** Thin vehicle shape used by home-screen recommendation lists (RentVehicle_m::vehicles_recomendation / promote_vehicles_recomendation). */
@Serializable
data class RecommendedVehicleItem(
    @SerialName("id") val id: Int,
    @SerialName("promote") val promote: Int = 0,
    @SerialName("regencies_id") val regenciesId: Int? = null,
    @SerialName("regencies_name") val regenciesName: String? = null,
    @SerialName("title") val title: String? = null,
    @SerialName("price") val price: Double = 0.0,
    @SerialName("price_with_driver_basic") val priceWithDriverBasic: Double = 0.0,
    @SerialName("price_with_driver_full") val priceWithDriverFull: Double = 0.0,
    @SerialName("with_driver") val withDriver: Int = 0,
    @SerialName("img") val img: String? = null,
    @SerialName("rating") val rating: Double? = null,
    @SerialName("total_review") val totalReview: Int? = null
)

@Serializable
data class NewsPreviewItem(
    @SerialName("id") val id: Int,
    @SerialName("title") val title: String? = null,
    @SerialName("img") val img: String? = null,
    @SerialName("status_name") val statusName: String? = null
)

@Serializable
data class HomeData(
    @SerialName("vehicles_recomendation") val vehiclesRecommendation: List<RecommendedVehicleItem> = emptyList(),
    @SerialName("promote_vehicles_recomendation") val promoteVehiclesRecommendation: List<RecommendedVehicleItem> = emptyList(),
    @SerialName("news_preview") val newsPreview: List<NewsPreviewItem> = emptyList(),
    /** Only present when the `key` header was sent (logged in). */
    @SerialName("balance") val balance: BalanceData? = null,
    @SerialName("referal_code") val referalCode: String? = null
)

@Serializable
data class PartnerFeatureItem(
    @SerialName("id") val id: Int? = null,
    @SerialName("account_id") val accountId: Int? = null,
    @SerialName("feature_id") val featureId: Int? = null,
    @SerialName("status") val status: Int? = null,
    @SerialName("name") val name: String? = null,
    @SerialName("status_name") val statusName: String? = null,
    @SerialName("icon") val icon: String? = null
)

@Serializable
data class AccountStatusData(
    @SerialName("partner_chat_unread") val partnerChatUnread: Int = 0,
    @SerialName("customer_chat_unread") val customerChatUnread: Int = 0,
    @SerialName("customer_status") val customerStatus: Int? = null,
    /** 0 = not a partner, 1 = active partner, see Partner_m::get_status for other codes (pending/rejected). */
    @SerialName("partner_status") val partnerStatus: Int? = null,
    @SerialName("maintenance") val maintenance: Int = 0,
    @SerialName("android_app_version_code") val androidAppVersionCode: Int = 0,
    @SerialName("android_app_version_name") val androidAppVersionName: String? = null,
    /** Only present when partner_status == 1. */
    @SerialName("partner_features") val partnerFeatures: List<PartnerFeatureItem>? = null
)

@Serializable
data class BalanceOnlyData(
    @SerialName("balance") val balance: Double = 0.0
)

@Serializable
data class PointOnlyData(
    @SerialName("point") val point: Int = 0
)

@Serializable
data class TopupConfigData(
    @SerialName("topup_minimum") val topupMinimum: Double = 0.0,
    @SerialName("banks") val banks: List<CompanyBank> = emptyList()
)

/** `company_bank.*` + joined bank name/code/icon -- destination accounts for topup transfers. */
@Serializable
data class CompanyBank(
    @SerialName("id") val id: Int,
    @SerialName("bank_id") val bankId: Int? = null,
    @SerialName("bank_number") val bankNumber: String? = null,
    @SerialName("name") val name: String? = null,
    @SerialName("bank_name") val bankName: String? = null,
    @SerialName("code") val code: String? = null,
    @SerialName("icon") val icon: String? = null
)

@Serializable
data class CreateTopupRequest(
    @SerialName("value") val value: Double,
    @SerialName("company_bank_id") val companyBankId: Int
)

@Serializable
data class TopupCreatedData(
    @SerialName("id") val id: Int,
    @SerialName("value_with_code") val valueWithCode: Double
)

/** `customer_topup.*` + joined bank/status names. */
@Serializable
data class TopupItem(
    @SerialName("id") val id: Int,
    @SerialName("account_id") val accountId: Int? = null,
    @SerialName("company_bank_id") val companyBankId: Int? = null,
    @SerialName("value") val value: Double = 0.0,
    @SerialName("value_with_code") val valueWithCode: Double = 0.0,
    @SerialName("img_proof") val imgProof: String? = null,
    @SerialName("processed") val processed: Int = 0,
    @SerialName("status") val status: Int? = null,
    @SerialName("date_added") val dateAdded: String? = null,
    @SerialName("bank_name") val bankName: String? = null,
    @SerialName("bank_code") val bankCode: String? = null,
    @SerialName("icon") val icon: String? = null,
    @SerialName("bank_number") val bankNumber: String? = null,
    @SerialName("name") val name: String? = null,
    @SerialName("status_name") val statusName: String? = null
)

@Serializable
data class TopupListData(
    @SerialName("topups") val topups: List<TopupItem> = emptyList()
)

@Serializable
data class TopupDetailData(
    @SerialName("detail") val detail: TopupItem
)

@Serializable
data class WithdrawConfigData(
    @SerialName("withdraw_minimum") val withdrawMinimum: Double = 0.0,
    @SerialName("banks") val banks: List<CustomerBank> = emptyList()
)

@Serializable
data class CreateWithdrawRequest(
    @SerialName("value") val value: Double,
    @SerialName("account_bank_id") val accountBankId: Int
)

@Serializable
data class WithdrawCreatedData(
    @SerialName("id") val id: Int
)

/** `customer_withdraw.*` + joined bank/status names. */
@Serializable
data class WithdrawItem(
    @SerialName("id") val id: Int,
    @SerialName("account_id") val accountId: Int? = null,
    @SerialName("account_bank_id") val accountBankId: Int? = null,
    @SerialName("value") val value: Double = 0.0,
    @SerialName("description") val description: String? = null,
    @SerialName("status") val status: Int? = null,
    @SerialName("processed") val processed: Int = 0,
    @SerialName("date_added") val dateAdded: String? = null,
    @SerialName("bank_name") val bankName: String? = null,
    @SerialName("bank_code") val bankCode: String? = null,
    @SerialName("icon") val icon: String? = null,
    @SerialName("bank_number") val bankNumber: String? = null,
    @SerialName("name") val name: String? = null,
    @SerialName("status_name") val statusName: String? = null
)

@Serializable
data class WithdrawListData(
    @SerialName("withdraws") val withdraws: List<WithdrawItem> = emptyList()
)

@Serializable
data class PointExchangeConfigData(
    @SerialName("exchange_point_minimum") val exchangePointMinimum: Int = 0,
    @SerialName("rate_point_to_balance") val ratePointToBalance: Double = 0.0
)

@Serializable
data class ExchangePointRequest(
    @SerialName("point") val point: Int
)

@Serializable
data class PointTransactionItem(
    @SerialName("id") val id: Int,
    @SerialName("transaction_id") val transactionId: Int? = null,
    @SerialName("account_id") val accountId: Int? = null,
    @SerialName("target_id") val targetId: Int? = null,
    @SerialName("point_debit") val pointDebit: Int? = null,
    @SerialName("point_credit") val pointCredit: Int? = null,
    @SerialName("description") val description: String? = null,
    @SerialName("date_added") val dateAdded: String? = null
)

@Serializable
data class PointTransactionsData(
    @SerialName("transaction_point") val transactionPoint: List<PointTransactionItem> = emptyList()
)

@Serializable
data class UpdateLocationRequest(
    @SerialName("latitude") val latitude: Double,
    @SerialName("longitude") val longitude: Double
)

@Serializable
data class UpdatePushTokenRequest(
    @SerialName("token") val token: String
)
