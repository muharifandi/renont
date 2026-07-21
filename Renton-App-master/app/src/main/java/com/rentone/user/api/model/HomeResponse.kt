package com.rentone.user.api.model

import com.rentone.user.domain.model.Balance
import com.rentone.user.domain.model.News
import com.rentone.user.domain.model.Vehicle
import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class HomeResponse(
    @SerialName("status") val status: Boolean,
    @SerialName("message") val message: String? = null,
    @SerialName("balance") val balance: Balance? = null,
    @SerialName("referal_code") val referralCode: String? = null,
    @SerialName("vehicles_recomendation") val vehiclesRecommendation: List<Vehicle> = emptyList(),
    @SerialName("promote_vehicles_recomendation") val promoteVehiclesRecommendation: List<Vehicle> = emptyList(),
    @SerialName("news_preview") val newsPreview: List<News> = emptyList()
)
