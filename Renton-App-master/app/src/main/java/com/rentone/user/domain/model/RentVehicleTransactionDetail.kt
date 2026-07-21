package com.rentone.user.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class RentVehicleTransactionDetail(
    val id: Int,
    val vehicle: Vehicle? = null,
    val pricePackage: Int = 0,
    val pricePackageName: String? = null,
    val price: Double = 0.0,
    val startDate: String? = null,
    val endDate: String? = null,
    val delivery: Int = 0,
    val deliveryDate: String? = null,
    val deliveryAddress: String? = null,
    val deliveryLatitude: Double = 0.0,
    val deliveryLongitude: Double = 0.0,
    val deliveryFee: Double = 0.0,
    val pickoff: Int = 0,
    val pickoffDate: String? = null,
    val pickoffAddress: String? = null,
    val pickoffLatitude: Double = 0.0,
    val pickoffLongitude: Double = 0.0,
    val pickoffFee: Double = 0.0,
    val voucher: Voucher? = null,
    val discount: Double = 0.0,
    val totalPayment: Double = 0.0,
    val cashOnDelivery: Int = 0,
    val overtime: Int = 0,
    val overtimeHour: Int = 0,
    val overtimeFee: Double = 0.0,
    val totalOvertimeFee: Double = 0.0,
    val adminFee: Double = 0.0,
    val description: String? = null,
    val status: Int = 0,
    val statusName: String? = null,
    val dateModified: String? = null
)
