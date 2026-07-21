package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class PackagePrice(
    val pricePackage: Int = 0,
    val selectorText: String? = null
) {
    override fun toString(): String = selectorText.orEmpty()
}
