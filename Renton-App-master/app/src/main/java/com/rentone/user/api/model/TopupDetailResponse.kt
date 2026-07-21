package com.rentone.user.api.model

import com.rentone.user.domain.model.Topup
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class TopupDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("detail") val detail: Topup? = null
)
