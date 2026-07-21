package com.rentone.user.domain.model

data class RequestWithdrawConfig(
    val withdrawMinimum: Double,
    val banks: List<CustomerBank>
)
