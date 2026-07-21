package com.rentone.user.data.mapper

import com.rentone.user.api.model.CheckVoucherResponse
import com.rentone.user.api.model.CheckoutDetailResponse
import com.rentone.user.api.model.ListVehicleResponse
import com.rentone.user.api.model.ListVehicleReviewResponse
import com.rentone.user.api.model.VehicleDetailResponse
import com.rentone.user.domain.model.*

fun ListVehicleResponse.toSearchResult(): VehicleSearchResult {
    return VehicleSearchResult(
        vehicles = vehicles,
        priceMin = priceMin,
        priceMax = priceMax,
        regencies = regencies
    )
}

fun VehicleDetailResponse.toDomain(): VehicleDetail {
    return VehicleDetail(
        vehicle = vehicle,
        vehicleBooked = vehicleBooked,
        partner = partner,
        reviews = reviews,
        reviewTotal = reviewTotal,
        forceWithDriver = forceWithDriver
    )
}

fun ListVehicleReviewResponse.toDomainList(): List<Review> {
    return reviews
}

fun CheckVoucherResponse.toDomain(): Voucher {
    return voucher ?: Voucher(id = 0, status = 0) // Simplified fallback
}

fun CheckoutDetailResponse.toDomain(): CheckoutDetail {
    return CheckoutDetail(
        vehicle = vehicle,
        config = config,
        rentPayment = rentPayment,
        days = days,
        startDate = startDate,
        endDate = endDate,
        cashOnDelivery = cashOnDelivery
    )
}
