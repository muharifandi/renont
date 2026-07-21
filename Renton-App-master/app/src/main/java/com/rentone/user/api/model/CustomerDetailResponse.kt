package com.rentone.user.api.model

import com.rentone.user.domain.model.Balance
import com.rentone.user.domain.model.CustomerDetail
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class CustomerDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("customer") val customerDetail: CustomerDetail? = null,
    @SerialName("balance") val balance: Balance? = null,
    @SerialName("bank_total") val bankTotal: Int = 0
)
