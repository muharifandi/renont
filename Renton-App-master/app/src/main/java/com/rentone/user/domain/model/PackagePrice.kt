package com.rentone.user.domain.model



data class PackagePrice(
    val pricePackage: Int = 0,
    val selectorText: String? = null
) {
    override fun toString(): String = selectorText.orEmpty()
}
