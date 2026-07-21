package com.rentone.user.domain.model



data class Regencies(
    val id: Int,
    val provinceId: Int = 0,
    val name: String
) {
    override fun toString(): String = name
}
