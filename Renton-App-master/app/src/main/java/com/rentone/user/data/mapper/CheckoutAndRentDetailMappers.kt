package com.rentone.user.data.mapper

import com.rentone.user.api.model.CheckoutDetailResponse
import com.rentone.user.api.model.RentVehicleDetailResponse
import com.rentone.user.domain.model.CheckoutDetail
import com.rentone.user.domain.model.RentVehicleDetail

fun CheckoutDetailResponse.toDomain(): CheckoutDetail = CheckoutDetail(
    vehicle = vehicle,
    config = config,
    rentPayment = rentPayment,
    days = days,
    startDate = startDate,
    endDate = endDate,
    cashOnDelivery = cashOnDelivery
)

fun RentVehicleDetailResponse.toDomain(): RentVehicleDetail = RentVehicleDetail(
    customerDetail = customerDetail,
    partnerDetail = partnerDetail,
    vehicle = vehicle,
    voucher = voucher,
    rentVehicleTransactionDetail = rentVehicleTransactionDetail,
    balance = balance,
    hourOvertime = hourOvertime,
    feedback = feedback
)
