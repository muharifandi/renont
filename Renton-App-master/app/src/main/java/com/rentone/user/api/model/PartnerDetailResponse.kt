package com.rentone.user.api.model

import com.rentone.user.domain.model.PartnerDetail
import com.rentone.user.domain.model.PartnerFeature
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PartnerDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("partner") val partnerDetail: PartnerDetail? = null,
    @SerialName("features") val partnerFeatures: List<PartnerFeature> = emptyList()
)
