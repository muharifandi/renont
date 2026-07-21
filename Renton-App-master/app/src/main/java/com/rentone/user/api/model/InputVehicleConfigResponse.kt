package com.rentone.user.api.model

import com.rentone.user.domain.model.BasicData
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class InputVehicleConfigResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("vehicle_type") val vehicleType: List<BasicData> = emptyList(),
    @SerialName("brand") val brand: List<BasicData> = emptyList(),
    @SerialName("color") val color: List<BasicData> = emptyList(),
    @SerialName("transmition_type") val transmitionType: List<BasicData> = emptyList(),
    @SerialName("driven_type") val drivenType: List<BasicData> = emptyList(),
    @SerialName("fuel") val fuel: List<BasicData> = emptyList()
)
