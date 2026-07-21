package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.PartnerReward
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PartnerRewardDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("data") val partnerRewards: List<PartnerReward> = emptyList()
)
