package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.RentVehicleConfig
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PartnerRentVehicleConfigResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("rent_config") val rentVehicleConfig: RentVehicleConfig? = null
)
