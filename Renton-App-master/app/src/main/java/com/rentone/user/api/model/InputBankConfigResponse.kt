package com.rentone.user.api.model

import com.rentone.user.domain.model.Bank
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class InputBankConfigResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("banks") val banks: List<Bank> = emptyList()
)
