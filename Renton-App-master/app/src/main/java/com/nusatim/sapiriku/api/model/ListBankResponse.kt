package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.Bank
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ListBankResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("banks") val banks: List<Bank> = emptyList()
)
