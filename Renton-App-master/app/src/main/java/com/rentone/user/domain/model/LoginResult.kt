package com.rentone.user.domain.model

data class LoginResult(
    val id: Int,
    val key: String,
    val message: String? = null
)
