package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.News
import com.nusatim.sapiriku.domain.model.Voucher
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class NewsDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("news") val news: News,
    @SerialName("voucher") val voucher: Voucher? = null
)
