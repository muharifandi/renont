package com.nusatim.sapiriku.api.model

import com.nusatim.sapiriku.domain.model.RentVehicleTransaction
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ListRentVehicleTransactionResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("transaction_rent_vehicle") val rentVehicleTransactions: List<RentVehicleTransaction> = emptyList()
)
