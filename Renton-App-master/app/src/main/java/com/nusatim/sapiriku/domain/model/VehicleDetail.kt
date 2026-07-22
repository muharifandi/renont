package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.InternalSerializationApi
import kotlinx.serialization.Serializable

@Serializable
@OptIn(InternalSerializationApi::class)
data class VehicleDetail(
    val vehicle: Vehicle,
    val vehicleBooked: List<DateRange>,
    val partner: PartnerDetail?,
    val reviews: List<Review>,
    val reviewTotal: Int,
    val forceWithDriver: Int
)
