package com.rentone.user.api.model

import com.rentone.user.domain.model.Voucher
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class CheckVoucherResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("voucher") val voucher: Voucher? = null
)
