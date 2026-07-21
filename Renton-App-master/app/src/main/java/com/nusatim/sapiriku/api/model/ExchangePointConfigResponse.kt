package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ExchangePointConfigResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("exchange_point_minimum") val exchangePointMinimum: Int = 0,
    @SerialName("rate_point_to_balance") val ratePointToBalance: Double = 0.0
)
