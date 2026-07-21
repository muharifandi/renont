package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class Balance(
    val balance: Double = 0.0,
    val point: Int = 0
) {
    override fun toString(): String = balance.toString()
}
