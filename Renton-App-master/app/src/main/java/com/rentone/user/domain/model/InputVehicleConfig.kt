package com.rentone.user.domain.model

data class InputVehicleConfig(
    val vehicleType: List<BasicData>,
    val brand: List<BasicData>,
    val color: List<BasicData>,
    val transmitionType: List<BasicData>,
    val drivenType: List<BasicData>,
    val fuel: List<BasicData>
)
