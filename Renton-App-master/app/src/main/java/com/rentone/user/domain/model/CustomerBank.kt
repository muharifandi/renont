package com.rentone.user.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class CustomerBank(
    val id: Int,
    val accountId: Int = 0,
    val icon: String? = null,
    val bankId: Int = 0,
    val bankName: String? = null,
    val code: String? = null,
    val bankNumber: String? = null,
    val name: String? = null
) {
    override fun toString(): String = "$bankName - $name"
}
