package com.rentone.user.domain.model



data class CustomerDetail(
    val id: Int,
    val accountId: Int = 0,
    val firstName: String? = null,
    val lastName: String? = null,
    val phone: String? = null,
    val identityNumber: String? = null,
    val imgProfile: String? = null,
    val imgIdentity: String? = null,
    val memberSince: String? = null
)
