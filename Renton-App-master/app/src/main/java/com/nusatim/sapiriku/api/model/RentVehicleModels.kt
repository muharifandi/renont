package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

/** Thin shape used by the search/list endpoint (RentVehicle_m::list_vehicle). */
@Serializable
data class VehicleListItem(
    @SerialName("id") val id: Int,
    @SerialName("title") val title: String? = null,
    @SerialName("functional_type") val functionalType: Int? = null,
    @SerialName("vehicle_type_name") val vehicleTypeName: String? = null,
    @SerialName("with_driver") val withDriver: Int = 0,
    @SerialName("max_passenger") val maxPassenger: Int? = null,
    @SerialName("color_name") val colorName: String? = null,
    @SerialName("color_value") val colorValue: String? = null,
    @SerialName("price") val price: Double = 0.0,
    @SerialName("price_with_driver_basic") val priceWithDriverBasic: Double = 0.0,
    @SerialName("price_with_driver_full") val priceWithDriverFull: Double = 0.0,
    @SerialName("img") val img: String? = null,
    @SerialName("rating") val rating: Double? = null,
    @SerialName("total_review") val totalReview: Int? = null
)

@Serializable
data class FunctionalTypeItem(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null
)

@Serializable
data class VehicleListData(
    @SerialName("vehicles") val vehicles: List<VehicleListItem> = emptyList(),
    @SerialName("price_min") val priceMin: Double? = null,
    @SerialName("price_max") val priceMax: Double? = null,
    @SerialName("functional_type") val functionalType: List<FunctionalTypeItem> = emptyList(),
    @SerialName("regency") val regency: String? = null
)

/** Full shape used by detail/quote (RentVehicle_m::vehicle_detail -- rent_vehicles_item.* + joined names/icons). */
@Serializable
data class VehicleDetail(
    @SerialName("id") val id: Int,
    @SerialName("account_id") val accountId: Int? = null,
    @SerialName("functional_type") val functionalType: Int? = null,
    @SerialName("vehicle_type") val vehicleType: Int? = null,
    @SerialName("title") val title: String? = null,
    @SerialName("brand_id") val brandId: Int? = null,
    @SerialName("vehicle_model") val vehicleModel: Int? = null,
    @SerialName("year") val year: Int? = null,
    @SerialName("color_id") val colorId: Int? = null,
    @SerialName("max_passenger") val maxPassenger: Int? = null,
    @SerialName("max_baggage") val maxBaggage: Int = 0,
    @SerialName("driven_type") val drivenType: Int? = null,
    @SerialName("transmition_type") val transmitionType: Int? = null,
    @SerialName("fuel_type") val fuelType: Int? = null,
    @SerialName("price") val price: Double = 0.0,
    @SerialName("with_driver") val withDriver: Int? = null,
    @SerialName("price_with_driver_basic") val priceWithDriverBasic: Double = 0.0,
    @SerialName("price_with_driver_full") val priceWithDriverFull: Double = 0.0,
    @SerialName("delivered") val delivered: Int = 0,
    @SerialName("pickoff") val pickoff: Int = 0,
    @SerialName("status") val status: Int = 1,
    @SerialName("vehicle_type_name") val vehicleTypeName: String? = null,
    @SerialName("vehicle_type_icon") val vehicleTypeIcon: String? = null,
    @SerialName("brand_name") val brandName: String? = null,
    @SerialName("brand_icon") val brandIcon: String? = null,
    @SerialName("vehicle_model_name") val vehicleModelName: String? = null,
    @SerialName("color_name") val colorName: String? = null,
    @SerialName("color_value") val colorValue: String? = null,
    @SerialName("driven_type_name") val drivenTypeName: String? = null,
    @SerialName("driven_type_icon") val drivenTypeIcon: String? = null,
    @SerialName("transmition_type_name") val transmitionTypeName: String? = null,
    @SerialName("transmition_type_icon") val transmitionTypeIcon: String? = null,
    @SerialName("fuel_type_name") val fuelTypeName: String? = null,
    @SerialName("fuel_type_icon") val fuelTypeIcon: String? = null,
    @SerialName("status_name") val statusName: String? = null,
    @SerialName("photos") val photos: List<VehiclePhoto> = emptyList()
)

@Serializable
data class VehiclePhoto(
    @SerialName("id") val id: Int,
    @SerialName("item_id") val itemId: Int? = null,
    @SerialName("img") val img: String? = null
)

@Serializable
data class BookedDateRange(
    @SerialName("start_date") val startDate: String,
    @SerialName("end_date") val endDate: String
)

@Serializable
data class VehicleReview(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("img_profile") val imgProfile: String? = null,
    @SerialName("comment") val comment: String? = null,
    @SerialName("rating") val rating: Int? = null,
    @SerialName("date_modified") val dateModified: String? = null
)

@Serializable
data class PartnerInfo(
    @SerialName("id") val id: Int,
    @SerialName("account_id") val accountId: Int? = null,
    @SerialName("company_name") val companyName: String? = null,
    @SerialName("img_profile") val imgProfile: String? = null,
    @SerialName("description") val description: String? = null,
    @SerialName("regencies_name") val regenciesName: String? = null,
    @SerialName("ownership_name") val ownershipName: String? = null,
    @SerialName("address") val address: String? = null,
    @SerialName("latitude") val latitude: Double? = null,
    @SerialName("longitude") val longitude: Double? = null
)

@Serializable
data class PartnerRentConfig(
    @SerialName("force_with_driver") val forceWithDriver: Int = 0,
    @SerialName("max_day_cod") val maxDayCod: Int = 0,
    @SerialName("force_disable_delivery") val forceDisableDelivery: Int = 0,
    @SerialName("delivery_fee") val deliveryFee: Double = 0.0,
    @SerialName("force_disable_pickoff") val forceDisablePickoff: Int = 0,
    @SerialName("pickoff_fee") val pickoffFee: Double = 0.0,
    @SerialName("overtime_fee") val overtimeFee: Double = 0.0
)

@Serializable
data class VehicleDetailData(
    @SerialName("vehicle") val vehicle: VehicleDetail,
    @SerialName("vehicle_booked") val vehicleBooked: List<BookedDateRange> = emptyList(),
    @SerialName("force_with_driver") val forceWithDriver: Int = 0,
    @SerialName("partner") val partner: PartnerInfo? = null,
    @SerialName("review") val review: List<VehicleReview> = emptyList(),
    @SerialName("review_total") val reviewTotal: Int = 0
)

@Serializable
data class VehicleReviewsData(
    @SerialName("review") val review: List<VehicleReview> = emptyList(),
    @SerialName("review_total") val reviewTotal: Int = 0
)

@Serializable
data class QuoteData(
    @SerialName("vehicle") val vehicle: VehicleDetail,
    @SerialName("rent_payment") val rentPayment: Double = 0.0,
    @SerialName("days") val days: Int = 0,
    @SerialName("start_date") val startDate: String? = null,
    @SerialName("end_date") val endDate: String? = null,
    @SerialName("config") val config: PartnerRentConfig? = null,
    @SerialName("cash_on_delivery") val cashOnDelivery: Int = 0
)

@Serializable
data class VoucherCheckData(
    @SerialName("voucher") val voucher: VoucherItem
)

@Serializable
data class BookingCreatedData(
    @SerialName("id") val id: Int
)

@Serializable
data class QuoteRequest(
    @SerialName("vehicle_id") val vehicleId: Int,
    /** 0 = car only, 1 = car + basic driver, 2 = car + full driver */
    @SerialName("price_package") val pricePackage: Int,
    @SerialName("start_date") val startDate: String,
    @SerialName("end_date") val endDate: String
)

@Serializable
data class VoucherCheckRequest(
    @SerialName("code") val code: String,
    @SerialName("start_date") val startDate: String? = null
)

@Serializable
data class CreateBookingRequest(
    @SerialName("item_id") val itemId: Int,
    @SerialName("price_package") val pricePackage: Int,
    @SerialName("start_date") val startDate: String,
    @SerialName("end_date") val endDate: String,
    @SerialName("time") val time: String? = null,
    @SerialName("delivery") val delivery: Int? = null,
    @SerialName("delivery_time") val deliveryTime: String? = null,
    @SerialName("delivery_address") val deliveryAddress: String? = null,
    @SerialName("delivery_latitude") val deliveryLatitude: Double? = null,
    @SerialName("delivery_longitude") val deliveryLongitude: Double? = null,
    @SerialName("pickoff") val pickoff: Int? = null,
    @SerialName("pickoff_time") val pickoffTime: String? = null,
    @SerialName("pickoff_address") val pickoffAddress: String? = null,
    @SerialName("pickoff_latitude") val pickoffLatitude: Double? = null,
    @SerialName("pickoff_longitude") val pickoffLongitude: Double? = null,
    @SerialName("voucher_id") val voucherId: Int? = null,
    @SerialName("cash_on_delivery") val cashOnDelivery: Int? = null,
    @SerialName("description") val description: String? = null
)
