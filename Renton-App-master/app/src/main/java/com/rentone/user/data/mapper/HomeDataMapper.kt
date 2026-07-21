package com.rentone.user.data.mapper

import com.rentone.user.api.model.HomeResponse
import com.rentone.user.domain.model.HomeData

fun HomeResponse.toDomain(): HomeData = HomeData(
    balance = balance,
    referralCode = referralCode,
    vehiclesRecommendation = vehiclesRecommendation,
    promoteVehiclesRecommendation = promoteVehiclesRecommendation,
    newsPreview = newsPreview
)
