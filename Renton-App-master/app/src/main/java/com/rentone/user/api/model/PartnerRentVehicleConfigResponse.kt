package com.rentone.user.api.model

import com.rentone.user.domain.model.RentVehicleConfig
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PartnerRentVehicleConfigResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("rent_config") val rentVehicleConfig: RentVehicleConfig? = null
)
