package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.News
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ListNewsResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("news") val news: List<News> = emptyList()
)
