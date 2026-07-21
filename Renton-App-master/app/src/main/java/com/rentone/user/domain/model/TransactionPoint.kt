package com.rentone.user.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class TransactionPoint(
    val id: Int,
    val targetId: Int = 0,
    val pointDebit: Int = 0,
    val pointCredit: Int = 0,
    val description: String? = null,
    val date: String? = null
)
