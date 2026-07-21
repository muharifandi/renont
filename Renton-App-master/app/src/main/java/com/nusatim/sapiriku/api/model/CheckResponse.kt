package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class CheckEmailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("use_email") val useEmail: Boolean = false,
    @SerialName("message") val message: String? = null,
    @SerialName("additional_info") val additionalInfo: String? = null
)

@Serializable
data class CheckPhoneResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("use_phone") val usePhone: Boolean = false,
    @SerialName("message") val message: String? = null,
    @SerialName("additional_info") val additionalInfo: String? = null
)

@Serializable
data class CheckAgentResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("valid") val valid: Boolean = false,
    @SerialName("message") val message: String? = null,
    @SerialName("additional_info") val additionalInfo: String? = null
)
