package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.Review
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ListVehicleReviewResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("review_total") val reviewTotal: Int = 0,
    @SerialName("review") val reviews: List<Review> = emptyList()
)
