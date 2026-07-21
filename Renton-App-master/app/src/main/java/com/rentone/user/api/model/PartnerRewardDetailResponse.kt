package com.rentone.user.api.model

import com.rentone.user.domain.model.PartnerReward
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PartnerRewardDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("data") val partnerRewards: List<PartnerReward> = emptyList()
)
