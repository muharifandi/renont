package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class BasicResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null
)
