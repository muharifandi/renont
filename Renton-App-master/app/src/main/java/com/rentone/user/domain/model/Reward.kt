package com.rentone.user.domain.model



data class Reward(
    val id: Int,
    val title: String? = null,
    val description: String? = null,
    val img: String? = null,
    val rewardType: Int = 0,
    val target: Int = 0,
    val pointReward: Int = 0,
    val aquired: Int = 0,
    val processed: Int = 0,
    val claimed: Int = 0,
    val rewardId: Int = 0
)