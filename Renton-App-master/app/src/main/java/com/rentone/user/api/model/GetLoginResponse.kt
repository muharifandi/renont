package com.rentone.user.api.model

import kotlinx.serialization.Serializable
import kotlinx.serialization.SerialName

@Serializable
data class GetLoginResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("id") val id: Int = 0,
    @SerialName("key") val key: String = ""
)
