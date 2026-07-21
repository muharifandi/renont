package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable

@Serializable
data class PartnerVehicleSearchResult(
    val vehicles: List<Vehicle> = emptyList(),
    val functionalType: List<BasicData> = emptyList(),
    val priceMin: Double = 0.0,
    val priceMax: Double = 0.0
)
