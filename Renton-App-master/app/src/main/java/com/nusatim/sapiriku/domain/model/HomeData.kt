package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.InternalSerializationApi
import kotlinx.serialization.Serializable

@Serializable
@OptIn(InternalSerializationApi::class)
data class HomeData(
    val balance: Balance?,
    val referralCode: String?,
    val vehiclesRecommendation: List<Vehicle>,
    val promoteVehiclesRecommendation: List<Vehicle>,
    val newsPreview: List<News>
)
