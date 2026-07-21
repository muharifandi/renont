package com.rentone.user.api.model

import com.rentone.user.domain.model.Withdraw
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ListWithdrawResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("withdraws") val withdraws: List<Withdraw> = emptyList()
)
