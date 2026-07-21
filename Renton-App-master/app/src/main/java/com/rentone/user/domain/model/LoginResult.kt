package com.rentone.user.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class LoginResult(
    val id: Int,
    val key: String,
    val message: String? = null
)
