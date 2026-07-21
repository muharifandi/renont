package com.rentone.user.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class RequestWithdrawConfig(
    val withdrawMinimum: Double,
    val banks: List<CustomerBank>
)
