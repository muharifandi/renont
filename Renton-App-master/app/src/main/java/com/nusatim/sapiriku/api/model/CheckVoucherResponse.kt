package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.Voucher
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class CheckVoucherResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("voucher") val voucher: Voucher? = null
)
