package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class InputVehicleConfig(
    val vehicleType: List<BasicData>,
    val brand: List<BasicData>,
    val color: List<BasicData>,
    val transmitionType: List<BasicData>,
    val drivenType: List<BasicData>,
    val fuel: List<BasicData>
)
