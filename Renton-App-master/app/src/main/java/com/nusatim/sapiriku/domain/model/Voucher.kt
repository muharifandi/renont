package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable


@Serializable
data class Voucher(
    val id: Int,
    val userType: Int = 0,
    val code: String? = null,
    val value: Double = 0.0,
    val description: String? = null,
    val useExpire: Int = 0,
    val startDate: String? = null,
    val endDate: String? = null,
    val useQuota: Int = 0,
    val quota: Int = 0,
    val status: Int = 0
)
