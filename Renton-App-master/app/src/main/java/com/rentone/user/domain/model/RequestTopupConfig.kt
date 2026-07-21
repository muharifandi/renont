package com.rentone.user.domain.model

data class RequestTopupConfig(
    val topupMinimum: Double,
    val banks: List<CompanyBank>
)
