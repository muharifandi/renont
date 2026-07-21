package com.rentone.user.domain.model



data class CompanyBank(
    val id: Int,
    val icon: String? = null,
    val bankId: Int = 0,
    val bankName: String? = null,
    val code: String? = null,
    val bankNumber: String? = null,
    val name: String? = null
) {
    override fun toString(): String = "$bankName - $name"
}