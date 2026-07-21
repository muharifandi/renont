package com.rentone.user.api.model

import com.rentone.user.domain.model.DateRange
import com.rentone.user.domain.model.PartnerDetail
import com.rentone.user.domain.model.Review
import com.rentone.user.domain.model.Vehicle
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class VehicleDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("vehicle") val vehicle: Vehicle,
    @SerialName("vehicle_booked") val vehicleBooked: List<DateRange> = emptyList(),
    @SerialName("partner") val partner: PartnerDetail? = null,
    @SerialName("reviews") val reviews: List<Review> = emptyList(),
    @SerialName("review_total") val reviewTotal: Int = 0,
    @SerialName("force_with_driver") val forceWithDriver: Int = 0
)
