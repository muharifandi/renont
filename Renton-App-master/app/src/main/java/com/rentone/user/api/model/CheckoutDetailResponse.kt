package com.rentone.user.api.model

import com.rentone.user.domain.model.RentVehicleConfig
import com.rentone.user.domain.model.Vehicle
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class CheckoutDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("vehicle") val vehicle: Vehicle? = null,
    @SerialName("config") val config: RentVehicleConfig? = null,
    @SerialName("rent_payment") val rentPayment: Double = 0.0,
    @SerialName("days") val days: Int = 0,
    @SerialName("start_date") val startDate: String? = null,
    @SerialName("end_date") val endDate: String? = null,
    @SerialName("cash_on_delivery") val cashOnDelivery: Int = 0
)
