package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class RewardScope(
    @SerialName("id") val id: Int,
    @SerialName("name") val name: String? = null,
    @SerialName("start") val start: String? = null,
    @SerialName("end") val end: String? = null
)

@Serializable
data class RewardScopesData(
    @SerialName("data") val data: List<RewardScope> = emptyList()
)

@Serializable
data class FeatureRewardProgress(
    @SerialName("feature_name") val featureName: String? = null,
    @SerialName("transaction_success") val transactionSuccess: Int = 0,
    @SerialName("rewards") val rewards: List<PartnerRewardItem> = emptyList()
)

@Serializable
data class PartnerRewardItem(
    @SerialName("id") val id: Int,
    @SerialName("title") val title: String? = null,
    @SerialName("img") val img: String? = null,
    @SerialName("reward_type") val rewardType: Int? = null,
    @SerialName("target") val target: Int = 0,
    @SerialName("point_reward") val pointReward: Int? = null,
    @SerialName("aquired") val acquired: Int = 0,
    @SerialName("processed") val processed: Int? = null,
    @SerialName("claimed") val claimed: Int? = null,
    @SerialName("reward_id") val historyRewardId: Int? = null
)

@Serializable
data class PartnerRewardDetailData(
    @SerialName("data") val data: List<FeatureRewardProgress> = emptyList()
)

@Serializable
data class ClaimRewardRequest(
    @SerialName("reward_id") val rewardId: Int
)
