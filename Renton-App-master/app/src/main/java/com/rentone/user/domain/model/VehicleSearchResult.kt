package com.rentone.user.domain.model

data class VehicleSearchResult(
    val vehicles: List<Vehicle>,
    val priceMin: Double,
    val priceMax: Double,
    val regencies: String? = null
)
