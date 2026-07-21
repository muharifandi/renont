package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class RequestWithdrawConfig(
    val withdrawMinimum: Double,
    val banks: List<CustomerBank>
)
