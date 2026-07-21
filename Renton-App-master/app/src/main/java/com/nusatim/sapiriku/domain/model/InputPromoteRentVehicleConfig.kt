package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class InputPromoteRentVehicleConfig(
    val info: String?,
    val pricePerDay: Double,
    val vehicles: List<Vehicle>
)
