package com.rentone.user.domain.model

data class InputPromoteRentVehicleConfig(
    val info: String?,
    val pricePerDay: Double,
    val vehicles: List<Vehicle>
)
