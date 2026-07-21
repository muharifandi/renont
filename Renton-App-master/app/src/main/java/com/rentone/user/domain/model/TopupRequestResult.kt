package com.rentone.user.domain.model

data class TopupRequestResult(
    val success: Boolean,
    val message: String?,
    val topupId: Int
)
