package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
import java.io.Serializable as JavaSerializable


@Serializable
data class BasicData(
    val id: Int,
    val name: String,
    val icon: String? = null,
    val value: String? = null
) : JavaSerializable {
    override fun toString(): String = name
}
