package com.rentone.user.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class InputPromoteRentVehicleConfig(
    val info: String?,
    val pricePerDay: Double,
    val vehicles: List<Vehicle>
)
