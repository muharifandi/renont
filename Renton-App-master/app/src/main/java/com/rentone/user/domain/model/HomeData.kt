package com.rentone.user.domain.model

data class HomeData(
    val balance: Balance?,
    val referralCode: String?,
    val vehiclesRecommendation: List<Vehicle>,
    val promoteVehiclesRecommendation: List<Vehicle>,
    val newsPreview: List<News>
)
