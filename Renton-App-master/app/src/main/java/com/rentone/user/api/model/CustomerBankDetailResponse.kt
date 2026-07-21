package com.rentone.user.api.model

import com.rentone.user.domain.model.CustomerBank
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class CustomerBankDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("bank") val bank: CustomerBank? = null
)
