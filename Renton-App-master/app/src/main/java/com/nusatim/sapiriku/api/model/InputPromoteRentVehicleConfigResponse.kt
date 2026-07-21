package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.Vehicle
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class InputPromoteRentVehicleConfigResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("info") val info: String? = null,
    @SerialName("price_per_day") val pricePerDay: Double = 0.0,
    @SerialName("vehicles") val vehicles: List<Vehicle> = emptyList()
)
