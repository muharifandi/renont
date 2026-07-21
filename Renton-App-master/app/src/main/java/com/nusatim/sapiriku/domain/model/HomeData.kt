package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class HomeData(
    val balance: Balance?,
    val referralCode: String?,
    val vehiclesRecommendation: List<Vehicle>,
    val promoteVehiclesRecommendation: List<Vehicle>,
    val newsPreview: List<News>
)
