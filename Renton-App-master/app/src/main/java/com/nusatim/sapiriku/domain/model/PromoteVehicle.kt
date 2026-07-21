package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class PromoteVehicle(
    val id: Int,
    val itemId: Int = 0,
    val img: String? = null,
    val title: String? = null,
    val startDate: String? = null,
    val endDate: String? = null,
    val pricePerDay: Double = 0.0,
    val days: Int = 0,
    val totalPayment: Double = 0.0,
    val canceledTotalReturn: Double = 0.0,
    val viewer: Int = 0,
    val status: Int = 0,
    val statusName: String? = null,
    val dateAdded: String? = null
)
