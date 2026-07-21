package com.rentone.user.data.mapper

import com.rentone.user.api.model.CustomerDetailResponse
import com.rentone.user.api.model.NewsDetailResponse
import com.rentone.user.api.model.PartnerDetailResponse
import com.rentone.user.api.model.VehicleDetailResponse
import com.rentone.user.domain.model.CustomerAccountDetail
import com.rentone.user.domain.model.NewsDetail
import com.rentone.user.domain.model.PartnerAccountDetail
import com.rentone.user.domain.model.VehicleDetail

fun VehicleDetailResponse.toDomain(): VehicleDetail = VehicleDetail(
    vehicle = vehicle,
    vehicleBooked = vehicleBooked,
    partner = partner,
    reviews = reviews,
    reviewTotal = reviewTotal,
    forceWithDriver = forceWithDriver
)

fun CustomerDetailResponse.toDomain(): CustomerAccountDetail = CustomerAccountDetail(
    customerDetail = customerDetail,
    balance = balance,
    bankTotal = bankTotal
)

fun PartnerDetailResponse.toDomain(): PartnerAccountDetail = PartnerAccountDetail(
    partnerDetail = partnerDetail,
    partnerFeatures = partnerFeatures
)

fun NewsDetailResponse.toDomain(): NewsDetail = NewsDetail(
    news = news,
    voucher = voucher
)
