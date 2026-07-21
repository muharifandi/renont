package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class RequestTopupConfig(
    val topupMinimum: Double,
    val banks: List<CompanyBank>
)
