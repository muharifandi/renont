package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.CompanyBank
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class RequestTopupConfigResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("topup_minimum") val topupMinimum: Double = 0.0,
    @SerialName("banks") val banks: List<CompanyBank> = emptyList()
)
