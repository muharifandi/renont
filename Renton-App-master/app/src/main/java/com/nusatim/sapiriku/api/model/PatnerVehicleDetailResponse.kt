package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.Vehicle
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PatnerVehicleDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("vehicle") val vehicle: Vehicle? = null
)
