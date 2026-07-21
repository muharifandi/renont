package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class TopupRequestResult(
    val success: Boolean,
    val message: String?,
    val topupId: Int
)
