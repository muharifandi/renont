package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class UploadImageResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("id") val id: Int = 0,
    @SerialName("filename") val filename: String? = null
)
