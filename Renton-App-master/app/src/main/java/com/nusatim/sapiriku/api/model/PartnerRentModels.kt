package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class VehicleTypeOption(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("functional_type") val functionalType: Int? = null,
    @SerialName("icon") val icon: String? = null
)

@Serializable
data class BrandOption(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("functional_type") val functionalType: Int? = null,
    @SerialName("icon") val icon: String? = null
)

@Serializable
data class VehicleModelOption(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("brand_id") val brandId: Int? = null
)

@Serializable
data class ColorOption(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("value") val value: String? = null
)

@Serializable
data class TransmitionTypeOption(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("functional_type") val functionalType: Int? = null,
    @SerialName("icon") val icon: String? = null
)

@Serializable
data class DrivenTypeOption(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("functional_type") val functionalType: Int? = null,
    @SerialName("icon") val icon: String? = null
)

@Serializable
data class FuelOption(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("icon") val icon: String? = null
)

@Serializable
data class VehicleInputConfigData(
    @SerialName("vehicle_type") val vehicleType: List<VehicleTypeOption> = emptyList(),
    @SerialName("brand") val brand: List<BrandOption> = emptyList(),
    @SerialName("color") val color: List<ColorOption> = emptyList(),
    @SerialName("transmition_type") val transmitionType: List<TransmitionTypeOption> = emptyList(),
    @SerialName("driven_type") val drivenType: List<DrivenTypeOption> = emptyList(),
    @SerialName("fuel") val fuel: List<FuelOption> = emptyList()
)

@Serializable
data class VehicleModelsData(
    @SerialName("data") val data: List<VehicleModelOption> = emptyList()
)

@Serializable
data class FunctionalTypesData(
    @SerialName("type") val type: List<FunctionalTypeItem> = emptyList()
)

/** Vehicle create/update payload -- POST/PUT partnerRent/vehicles. Photos are attached separately
 *  via vehicle_photos_post (upload) then their returned filenames go in [photos] here. */
@Serializable
data class VehiclePayloadRequest(
    @SerialName("title") val title: String,
    @SerialName("vehicle_type") val vehicleType: Int,
    @SerialName("brand_id") val brandId: Int,
    @SerialName("vehicle_model") val vehicleModel: Int,
    @SerialName("max_passenger") val maxPassenger: Int,
    @SerialName("max_baggage") val maxBaggage: Int? = null,
    @SerialName("year") val year: Int,
    @SerialName("color_id") val colorId: Int,
    @SerialName("transmition_type") val transmitionType: Int,
    @SerialName("driven_type") val drivenType: Int,
    @SerialName("fuel_type") val fuelType: Int,
    @SerialName("price") val price: Double,
    @SerialName("price_with_driver_basic") val priceWithDriverBasic: Double? = null,
    @SerialName("price_with_driver_full") val priceWithDriverFull: Double? = null,
    @SerialName("with_driver") val withDriver: Int? = null,
    @SerialName("delivered") val delivered: Int? = null,
    @SerialName("pickoff") val pickoff: Int? = null,
    @SerialName("functional_type") val functionalType: Int,
    @SerialName("status") val status: Int? = null,
    @SerialName("photos") val photos: List<String>? = null
)

@Serializable
data class VehicleCreatedData(
    @SerialName("id") val id: Int
)

@Serializable
data class PartnerVehicleListData(
    @SerialName("vehicles") val vehicles: List<VehicleListItem> = emptyList(),
    @SerialName("price_min") val priceMin: Double? = null,
    @SerialName("price_max") val priceMax: Double? = null,
    @SerialName("functional_type") val functionalType: List<FunctionalTypeItem> = emptyList()
)

@Serializable
data class PartnerVehicleDetailData(
    @SerialName("vehicle") val vehicle: VehicleDetail
)

@Serializable
data class UploadPhotoData(
    @SerialName("filename") val filename: String
)

@Serializable
data class PartnerRentConfigData(
    @SerialName("rent_config") val rentConfig: PartnerRentConfig? = null
)

@Serializable
data class UpdatePartnerRentConfigRequest(
    @SerialName("force_with_driver") val forceWithDriver: Int? = null,
    @SerialName("force_disable_delivery") val forceDisableDelivery: Int? = null,
    @SerialName("force_disable_pickoff") val forceDisablePickoff: Int? = null,
    @SerialName("delivery_fee") val deliveryFee: Double? = null,
    @SerialName("pickoff_fee") val pickoffFee: Double? = null,
    @SerialName("max_day_cod") val maxDayCod: Int? = null,
    @SerialName("overtime_fee") val overtimeFee: Double? = null
)

/** `promote_rent_vehicle.*` + joined vehicle title/img/status_name. */
@Serializable
data class PromoteItem(
    @SerialName("id") val id: Int,
    @SerialName("account_id") val accountId: Int? = null,
    @SerialName("item_id") val itemId: Int? = null,
    @SerialName("start_date") val startDate: String? = null,
    @SerialName("end_date") val endDate: String? = null,
    @SerialName("days") val days: Int = 0,
    @SerialName("price_per_day") val pricePerDay: Double = 0.0,
    @SerialName("total_payment") val totalPayment: Double = 0.0,
    @SerialName("canceled_total_return") val canceledTotalReturn: Double = 0.0,
    @SerialName("viewer") val viewer: Int = 0,
    @SerialName("status") val status: Int? = null,
    @SerialName("date_added") val dateAdded: String? = null,
    @SerialName("title") val title: String? = null,
    @SerialName("img") val img: String? = null,
    @SerialName("status_name") val statusName: String? = null
)

@Serializable
data class PromoteListData(
    @SerialName("promotes") val promotes: List<PromoteItem> = emptyList()
)

@Serializable
data class PromotionInputConfigData(
    @SerialName("vehicles") val vehicles: List<VehicleListItem> = emptyList(),
    @SerialName("info") val info: String? = null,
    @SerialName("price_per_day") val pricePerDay: Double = 0.0
)

@Serializable
data class CreatePromotionRequest(
    @SerialName("item_id") val itemId: Int,
    @SerialName("start_date") val startDate: String,
    @SerialName("end_date") val endDate: String
)

@Serializable
data class PromotionCreatedData(
    @SerialName("id") val id: Int
)
