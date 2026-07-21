package com.rentone.user.domain.model



data class Balance(
    val balance: Double = 0.0,
    val point: Int = 0
) {
    override fun toString(): String = balance.toString()
}
