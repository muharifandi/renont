package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.CustomerBank
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class RequestWithdrawConfigResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("withdraw_minimum") val withdrawMinimum: Double = 0.0,
    @SerialName("banks") val banks: List<CustomerBank> = emptyList()
)
