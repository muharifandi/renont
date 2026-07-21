package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class VehicleSearchResult(
    val vehicles: List<Vehicle> = emptyList(),
    val priceMin: Double = 0.0,
    val priceMax: Double = 0.0,
    val regencies: String? = null,
    val functionalType: List<BasicData> = emptyList()
)
