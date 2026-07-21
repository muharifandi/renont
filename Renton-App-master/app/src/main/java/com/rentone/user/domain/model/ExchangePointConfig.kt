package com.rentone.user.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class ExchangePointConfig(
    val exchangePointMinimum: Int,
    val ratePointToBalance: Double
)
