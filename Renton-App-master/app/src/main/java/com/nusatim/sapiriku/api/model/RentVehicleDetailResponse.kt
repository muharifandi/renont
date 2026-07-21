package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.Balance
import com.nusatim.sapiriku.domain.model.CustomerDetail
import com.nusatim.sapiriku.domain.model.PartnerDetail
import com.nusatim.sapiriku.domain.model.RentVehicleTransactionDetail
import com.nusatim.sapiriku.domain.model.Vehicle
import com.nusatim.sapiriku.domain.model.Voucher
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class RentVehicleDetailResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("customer") val customerDetail: CustomerDetail? = null,
    @SerialName("partner") val partnerDetail: PartnerDetail? = null,
    @SerialName("vehicle") val vehicle: Vehicle? = null,
    @SerialName("voucher") val voucher: Voucher? = null,
    @SerialName("transaction_detail") val rentVehicleTransactionDetail: RentVehicleTransactionDetail? = null,
    @SerialName("balance") val balance: Balance? = null,
    @SerialName("hour_overtime") val hourOvertime: Int = 0,
    @SerialName("feedback") val feedback: Int = 0
)
