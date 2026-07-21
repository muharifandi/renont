package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class Bank(
    val id: Int,
    val icon: String? = null,
    val name: String,
    val code: String
) {
    override fun toString(): String = "$code - $name"
}
