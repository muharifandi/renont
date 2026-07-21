package com.nusatim.sapiriku.domain.model.command

data class CheckoutCommand(
    val vehicleId: Int,
    val pricePackageId: Int,
    val startDate: String,
    val endDate: String,
    val voucherCode: String? = null,
    val notes: String? = null
)
