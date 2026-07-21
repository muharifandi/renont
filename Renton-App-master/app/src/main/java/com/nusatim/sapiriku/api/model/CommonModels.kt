package com.nusatim.sapiriku.api.model

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

/** `vouchers` table row -- shared across News, RentVehicle, PartnerRent responses. */
@Serializable
data class VoucherItem(
    @SerialName("id") val id: Int,
    @SerialName("feature_id") val featureId: Int? = null,
    @SerialName("user_type") val userType: Int? = null,
    @SerialName("code") val code: String? = null,
    @SerialName("voucher_type") val voucherType: Int? = null,
    @SerialName("value") val value: Double = 0.0,
    @SerialName("description") val description: String? = null,
    @SerialName("use_expire") val useExpire: Int = 0,
    @SerialName("start_date") val startDate: String? = null,
    @SerialName("end_date") val endDate: String? = null,
    @SerialName("use_quota") val useQuota: Int = 0,
    @SerialName("quota") val quota: Int = 0,
    @SerialName("status") val status: Int = 0
)
