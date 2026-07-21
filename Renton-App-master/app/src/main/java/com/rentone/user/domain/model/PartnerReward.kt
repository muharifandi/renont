package com.rentone.user.domain.model



data class PartnerReward(
    val featureName: String? = null,
    val rewards: List<Reward> = emptyList()
)
