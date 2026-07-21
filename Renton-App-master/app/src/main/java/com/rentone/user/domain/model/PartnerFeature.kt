package com.rentone.user.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class PartnerFeature(
    val id: Int,
    val featureId: Int,
    val icon: String? = null,
    val name: String? = null,
    val status: Int = 0,
    val statusName: String? = null
) {
    override fun toString(): String = name ?: ""
}
