package com.rentone.user.domain.model



data class Topup(
    val id: Int,
    val companyBankId: Int = 0,
    val bankName: String? = null,
    val bankCode: String? = null,
    val icon: String? = null,
    val bankNumber: String? = null,
    val name: String? = null,
    val value: Double = 0.0,
    val valueWithCode: Double = 0.0,
    val date: String? = null,
    val status: Int = 0,
    val statusName: String? = null
)
