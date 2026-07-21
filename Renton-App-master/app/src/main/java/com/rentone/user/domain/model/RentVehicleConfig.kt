package com.rentone.user.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class RentVehicleConfig(
    val forceWithDriver: Int = 0,
    val forceDisableDelivery: Int = 0,
    val forceDisablePickoff: Int = 0,
    val deliveryFee: Double? = null,
    val pickoffFee: Double? = null,
    val overtimeFee: Double? = null,
    val maxDayCod: Int = 0
)
