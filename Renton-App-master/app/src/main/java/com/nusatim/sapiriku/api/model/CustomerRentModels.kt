package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

/** Thin shape used by the booking list (CustomerRent_m::list_transaction / PartnerRent_m::list_transaction). */
@Serializable
data class BookingListItem(
    @SerialName("id") val id: Int,
    @SerialName("price_package_name") val pricePackageName: String? = null,
    @SerialName("start_date") val startDate: String? = null,
    @SerialName("end_date") val endDate: String? = null,
    @SerialName("total_payment") val totalPayment: Double = 0.0,
    @SerialName("date_modified") val dateModified: String? = null,
    @SerialName("vehicle_title") val vehicleTitle: String? = null,
    @SerialName("img") val img: String? = null,
    @SerialName("status_name") val statusName: String? = null
)

/** Full `transaction_rent_vehicle.*` row + joined status_name -- shared by CustomerRent and PartnerRent. */
@Serializable
data class TransactionDetail(
    @SerialName("id") val id: Int,
    @SerialName("account_id") val accountId: Int? = null,
    @SerialName("feature_id") val featureId: Int = 1,
    @SerialName("item_id") val itemId: Int? = null,
    @SerialName("price_package") val pricePackage: Int? = null,
    @SerialName("price_package_name") val pricePackageName: String? = null,
    @SerialName("price") val price: Double = 0.0,
    @SerialName("start_date") val startDate: String? = null,
    @SerialName("end_date") val endDate: String? = null,
    @SerialName("delivery") val delivery: Int = 0,
    @SerialName("delivery_date") val deliveryDate: String? = null,
    @SerialName("delivery_address") val deliveryAddress: String? = null,
    @SerialName("delivery_latitude") val deliveryLatitude: Double? = null,
    @SerialName("delivery_longitude") val deliveryLongitude: Double? = null,
    @SerialName("delivery_fee") val deliveryFee: Double = 0.0,
    @SerialName("pickoff") val pickoff: Int = 0,
    @SerialName("pickoff_date") val pickoffDate: String? = null,
    @SerialName("pickoff_address") val pickoffAddress: String? = null,
    @SerialName("pickoff_latitude") val pickoffLatitude: Double? = null,
    @SerialName("pickoff_longitude") val pickoffLongitude: Double? = null,
    @SerialName("pickoff_fee") val pickoffFee: Double = 0.0,
    @SerialName("voucher_id") val voucherId: Int? = null,
    @SerialName("discount") val discount: Double = 0.0,
    @SerialName("total_payment") val totalPayment: Double = 0.0,
    @SerialName("cash_on_delivery") val cashOnDelivery: Int = 0,
    @SerialName("overtime") val overtime: Int = 0,
    @SerialName("overtime_hour") val overtimeHour: Int = 0,
    @SerialName("overtime_fee") val overtimeFee: Double = 0.0,
    @SerialName("total_overtime_fee") val totalOvertimeFee: Double = 0.0,
    @SerialName("admin_fee") val adminFee: Double = 0.0,
    @SerialName("status") val status: Int? = null,
    @SerialName("description") val description: String? = null,
    @SerialName("date_added") val dateAdded: String? = null,
    @SerialName("date_modified") val dateModified: String? = null,
    @SerialName("status_name") val statusName: String? = null
)

@Serializable
data class BalanceData(
    @SerialName("balance") val balance: Double = 0.0,
    @SerialName("point") val point: Int = 0
)

@Serializable
data class BookingListData(
    @SerialName("transaction_rent_vehicle") val transactions: List<BookingListItem> = emptyList()
)

/** Customer's own info, as returned to the partner-side booking detail (Customer_m::customer_info). */
@Serializable
data class CustomerInfo(
    @SerialName("id") val id: Int,
    @SerialName("first_name") val firstName: String? = null,
    @SerialName("last_name") val lastName: String? = null,
    @SerialName("phone") val phone: String? = null,
    @SerialName("identity_number") val identityNumber: String? = null,
    @SerialName("img_profile") val imgProfile: String? = null,
    @SerialName("img_identity") val imgIdentity: String? = null
)

/** GET .../bookings/{id} detail -- shape shared by CustomerRent and PartnerRent (fields present differ slightly, all nullable). */
@Serializable
data class BookingDetailData(
    @SerialName("partner") val partner: PartnerInfo? = null,
    @SerialName("customer") val customer: CustomerInfo? = null,
    @SerialName("vehicle") val vehicle: VehicleDetail,
    @SerialName("transaction_detail") val transactionDetail: TransactionDetail,
    @SerialName("voucher") val voucher: VoucherItem? = null,
    @SerialName("balance") val balance: BalanceData? = null,
    @SerialName("hour_overtime") val hourOvertime: Int = 0,
    @SerialName("feedback") val feedback: Int = 0
)

@Serializable
data class UpdateBookingStatusRequest(
    @SerialName("status") val status: Int
)

@Serializable
data class BookingReviewRequest(
    @SerialName("rating") val rating: Int,
    @SerialName("comment") val comment: String? = null
)
