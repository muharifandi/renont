package com.rentone.user.api.model

import com.rentone.user.domain.model.BasicData
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class FunctionalTypeResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("type") val type: List<BasicData> = emptyList()
)
