package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.Topup
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class TopupDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("detail") val detail: Topup? = null
)
