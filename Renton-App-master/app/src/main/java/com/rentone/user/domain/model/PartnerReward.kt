package com.rentone.user.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class PartnerReward(
    val featureName: String? = null,
    val rewards: List<Reward> = emptyList()
)
