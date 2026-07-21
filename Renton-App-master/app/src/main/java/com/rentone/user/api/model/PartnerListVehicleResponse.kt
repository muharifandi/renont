package com.rentone.user.api.model

import com.rentone.user.domain.model.BasicData
import com.rentone.user.domain.model.Vehicle
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PartnerListVehicleResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("price_min") val priceMin: Double = 0.0,
    @SerialName("price_max") val priceMax: Double = 0.0,
    @SerialName("functional_type") val functionalType: List<BasicData> = emptyList(),
    @SerialName("vehicles") val vehicles: List<Vehicle> = emptyList()
)
