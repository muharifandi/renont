package com.rentone.user.domain.model

import java.io.Serializable as JavaSerializable


data class BasicData(
    val id: Int,
    val name: String,
    val icon: String? = null,
    val value: String? = null
) : JavaSerializable {
    override fun toString(): String = name
}
