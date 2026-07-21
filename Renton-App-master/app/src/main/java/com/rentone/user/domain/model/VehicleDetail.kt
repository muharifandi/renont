package com.rentone.user.domain.model

data class VehicleDetail(
    val vehicle: Vehicle,
    val vehicleBooked: List<DateRange>,
    val partner: PartnerDetail?,
    val reviews: List<Review>,
    val reviewTotal: Int,
    val forceWithDriver: Int
)
