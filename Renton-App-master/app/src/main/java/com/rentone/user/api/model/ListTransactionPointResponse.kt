package com.rentone.user.api.model

import com.rentone.user.domain.model.TransactionPoint
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class ListTransactionPointResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("transaction_point") val transactionPoints: List<TransactionPoint> = emptyList()
)
