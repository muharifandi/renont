package com.rentone.user.data.mapper

import com.rentone.user.api.model.ExchangePointConfigResponse
import com.rentone.user.api.model.InputPromoteRentVehicleConfigResponse
import com.rentone.user.api.model.InputVehicleConfigResponse
import com.rentone.user.api.model.RequestTopupConfigResponse
import com.rentone.user.api.model.RequestTopupResponse
import com.rentone.user.api.model.RequestWithdrawConfigResponse
import com.rentone.user.domain.model.ExchangePointConfig
import com.rentone.user.domain.model.InputPromoteRentVehicleConfig
import com.rentone.user.domain.model.InputVehicleConfig
import com.rentone.user.domain.model.RequestTopupConfig
import com.rentone.user.domain.model.RequestWithdrawConfig
import com.rentone.user.domain.model.TopupRequestResult

fun RequestTopupConfigResponse.toDomain(): RequestTopupConfig = RequestTopupConfig(
    topupMinimum = topupMinimum,
    banks = banks
)

fun RequestTopupResponse.toDomain(): TopupRequestResult = TopupRequestResult(
    success = status,
    message = message,
    topupId = topupId
)

fun ExchangePointConfigResponse.toDomain(): ExchangePointConfig = ExchangePointConfig(
    exchangePointMinimum = exchangePointMinimum,
    ratePointToBalance = ratePointToBalance
)

fun RequestWithdrawConfigResponse.toDomain(): RequestWithdrawConfig = RequestWithdrawConfig(
    withdrawMinimum = withdrawMinimum,
    banks = banks
)

fun InputVehicleConfigResponse.toDomain(): InputVehicleConfig = InputVehicleConfig(
    vehicleType = vehicleType,
    brand = brand,
    color = color,
    transmitionType = transmitionType,
    drivenType = drivenType,
    fuel = fuel
)

fun InputPromoteRentVehicleConfigResponse.toDomain(): InputPromoteRentVehicleConfig = InputPromoteRentVehicleConfig(
    info = info,
    pricePerDay = pricePerDay,
    vehicles = vehicles
)
