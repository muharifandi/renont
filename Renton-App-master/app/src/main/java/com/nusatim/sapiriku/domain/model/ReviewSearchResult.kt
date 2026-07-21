package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable

@Serializable
data class ReviewSearchResult(
    val reviews: List<Review> = emptyList(),
    val reviewTotal: Int = 0
)
