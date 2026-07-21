package com.rentone.user.domain.model

data class CustomerAccountDetail(
    val customerDetail: CustomerDetail?,
    val balance: Balance?,
    val bankTotal: Int
)
