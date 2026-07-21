package com.rentone.user.domain.model

data class RentVehicleDetail(
    val customerDetail: CustomerDetail?,
    val partnerDetail: PartnerDetail?,
    val vehicle: Vehicle?,
    val voucher: Voucher?,
    val rentVehicleTransactionDetail: RentVehicleTransactionDetail?,
    val balance: Balance?,
    val hourOvertime: Int,
    val feedback: Int
)
