package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class CustomerAccountDetail(
    val customerDetail: CustomerDetail?,
    val balance: Balance?,
    val bankTotal: Int
)
