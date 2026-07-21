package com.nusatim.sapiriku.data.mapper

import com.nusatim.sapiriku.api.model.*
import com.nusatim.sapiriku.domain.model.*

fun ListVehicleResponse.toSearchResult(): VehicleSearchResult = VehicleSearchResult(
    vehicles = vehicles,
    priceMin = priceMin,
    priceMax = priceMax,
    regencies = regencies,
    functionalType = functionalType
)

fun VehicleDetailResponse.toVehicleDetail(): VehicleDetail = VehicleDetail(
    vehicle = vehicle,
    vehicleBooked = vehicleBooked,
    partner = partner,
    reviews = reviews,
    reviewTotal = reviewTotal,
    forceWithDriver = forceWithDriver
)

fun CheckVoucherResponse.toVoucher(): Voucher = voucher ?: Voucher(id = 0, status = 0)

fun CheckoutDetailResponse.toCheckoutDetail(): CheckoutDetail = CheckoutDetail(
    vehicle = vehicle,
    config = config,
    rentPayment = rentPayment,
    days = days,
    startDate = startDate,
    endDate = endDate,
    cashOnDelivery = cashOnDelivery
)

fun RentVehicleDetailResponse.toRentVehicleDetail(): RentVehicleDetail = RentVehicleDetail(
    customerDetail = customerDetail,
    partnerDetail = partnerDetail,
    vehicle = vehicle,
    voucher = voucher,
    rentVehicleTransactionDetail = rentVehicleTransactionDetail,
    balance = balance,
    hourOvertime = hourOvertime,
    feedback = feedback
)

fun HomeResponse.toHomeData(): HomeData = HomeData(
    balance = balance,
    referralCode = referralCode,
    vehiclesRecommendation = vehiclesRecommendation,
    promoteVehiclesRecommendation = promoteVehiclesRecommendation,
    newsPreview = newsPreview
)

fun ApplicationStatusResponse.toApplicationStatus(): ApplicationStatus = ApplicationStatus(
    maintenance = maintenance == 1,
    maintenanceMessage = maintenanceMessage,
    androidAppVersionCode = androidAppVersionCode,
    androidAppVersionName = androidAppVersionName,
    androidAppUpdateLink = androidAppUpdateLink
)

fun BasicResponse.toOperationResult(): OperationResult = OperationResult(
    success = status,
    message = message
)

fun CheckEmailResponse.toValidationResult(): ValidationResult = ValidationResult(
    isValid = status,
    message = message,
    additionalInfo = additionalInfo
)

fun CheckPhoneResponse.toValidationResult(): ValidationResult = ValidationResult(
    isValid = status,
    message = message,
    additionalInfo = additionalInfo
)

fun CheckAgentResponse.toValidationResult(): ValidationResult = ValidationResult(
    isValid = status && valid,
    message = message,
    additionalInfo = additionalInfo
)

fun NewsDetailResponse.toNewsDetail(): NewsDetail = NewsDetail(
    news = news,
    voucher = voucher
)

fun RequestTopupConfigResponse.toRequestTopupConfig(): RequestTopupConfig = RequestTopupConfig(
    topupMinimum = topupMinimum,
    banks = banks
)

fun RequestTopupResponse.toTopupRequestResult(): TopupRequestResult = TopupRequestResult(
    success = status,
    message = message,
    topupId = topupId
)

fun ExchangePointConfigResponse.toExchangePointConfig(): ExchangePointConfig = ExchangePointConfig(
    exchangePointMinimum = exchangePointMinimum,
    ratePointToBalance = ratePointToBalance
)

fun RequestWithdrawConfigResponse.toRequestWithdrawConfig(): RequestWithdrawConfig = RequestWithdrawConfig(
    withdrawMinimum = withdrawMinimum,
    banks = banks
)

fun InputVehicleConfigResponse.toInputVehicleConfig(): InputVehicleConfig = InputVehicleConfig(
    vehicleType = vehicleType,
    brand = brand,
    color = color,
    transmitionType = transmitionType,
    drivenType = drivenType,
    fuel = fuel
)

fun InputPromoteRentVehicleConfigResponse.toInputPromoteRentVehicleConfig(): InputPromoteRentVehicleConfig = InputPromoteRentVehicleConfig(
    info = info,
    pricePerDay = pricePerDay,
    vehicles = vehicles
)

fun CustomerDetailResponse.toCustomerAccountDetail(): CustomerAccountDetail = CustomerAccountDetail(
    customerDetail = customerDetail,
    balance = balance,
    bankTotal = bankTotal
)

fun PartnerDetailResponse.toPartnerAccountDetail(): PartnerAccountDetail = PartnerAccountDetail(
    partnerDetail = partnerDetail,
    partnerFeatures = partnerFeatures
)
