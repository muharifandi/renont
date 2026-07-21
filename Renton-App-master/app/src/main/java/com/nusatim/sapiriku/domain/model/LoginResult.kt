package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class LoginResult(
    val id: Int,
    val key: String,
    val message: String? = null
)
