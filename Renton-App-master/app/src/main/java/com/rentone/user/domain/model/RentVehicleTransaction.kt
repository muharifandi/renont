package com.rentone.user.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class RentVehicleTransaction(
    val id: Int,
    val vehicleTitle: String? = null,
    val img: String? = null,
    val pricePackageName: String? = null,
    val startDate: String? = null,
    val endDate: String? = null,
    val totalPayment: Double = 0.0,
    val statusName: String? = null,
    val dateModified: String? = null
)
