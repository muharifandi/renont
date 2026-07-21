package com.rentone.user.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class TopupRequestResult(
    val success: Boolean,
    val message: String?,
    val topupId: Int
)
