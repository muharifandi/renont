package com.rentone.user.domain.model

data class CheckoutDetail(
    val vehicle: Vehicle?,
    val config: RentVehicleConfig?,
    val rentPayment: Double,
    val days: Int,
    val startDate: String?,
    val endDate: String?,
    val cashOnDelivery: Int
)
