package com.nusatim.sapiriku.data.mapper

import com.nusatim.sapiriku.api.model.*
import com.nusatim.sapiriku.domain.model.*

fun <T> ApiEnvelope<T>.toOperationResult(): OperationResult = OperationResult(
    success = status,
    message = message
)

fun ChatroomListItem.toChatroom(): Chatroom = Chatroom(
    id = id,
    name = name,
    imgProfile = imgProfile,
    unread = unread,
    message = message,
    dateAdded = dateAdded
)

fun ChatMessage.toChat(): Chat = Chat(
    id = id,
    chatroomId = chatroomId,
    userType = userType,
    accountId = accountId,
    attachmentType = attachmentType,
    attachment = attachment,
    message = message,
    dateAdded = dateAdded
)

fun QuoteData.toCheckoutDetail(): CheckoutDetail = CheckoutDetail(
    vehicle = vehicle.toVehicleDomain(),
    config = config?.toRentVehicleConfig(),
    rentPayment = rentPayment,
    days = days,
    startDate = startDate,
    endDate = endDate,
    cashOnDelivery = cashOnDelivery
)

fun VehicleInputConfigData.toInputVehicleConfig(): InputVehicleConfig = InputVehicleConfig(
    vehicleType = vehicleType.map { it.toBasicData() },
    brand = brand.map { it.toBasicData() },
    color = color.map { it.toBasicData() },
    transmitionType = transmitionType.map { it.toBasicData() },
    drivenType = drivenType.map { it.toBasicData() },
    fuel = fuel.map { it.toBasicData() }
)

fun ApplicationStatusData.toApplicationStatus(): ApplicationStatus = ApplicationStatus(
    maintenance = maintenance == 1,
    maintenanceMessage = maintenanceMessage,
    androidAppVersionCode = androidAppVersionCode,
    androidAppVersionName = androidAppVersionName,
    androidAppUpdateLink = androidAppUpdateLink
)

fun AvailabilityData.toValidationResult(message: String?): ValidationResult = ValidationResult(
    isValid = available,
    message = message
)

fun CheckAgentData.toValidationResult(): ValidationResult = ValidationResult(
    isValid = valid,
    message = name
)

fun RegencyItem.toRegencies(): Regencies = Regencies(
    id = id.toIntOrNull() ?: 0,
    name = name,
    provinceId = provinceId?.toIntOrNull() ?: 0
)

fun RegencyItem.toBasicData(): BasicData = BasicData(
    id = id.toIntOrNull() ?: 0,
    name = name
)

fun CustomerDetailData.toCustomerAccountDetail(): CustomerAccountDetail = CustomerAccountDetail(
    customerDetail = customer?.toCustomerDetailDomain(),
    balance = balance?.toBalance(),
    bankTotal = bankTotal
)

fun com.nusatim.sapiriku.api.model.CustomerDetail.toCustomerDetailDomain(): com.nusatim.sapiriku.domain.model.CustomerDetail = com.nusatim.sapiriku.domain.model.CustomerDetail(
    id = id,
    firstName = firstName,
    lastName = lastName,
    imgProfile = imgProfile,
    memberSince = memberSince
)

fun PartnerDetailData.toPartnerAccountDetail(): PartnerAccountDetail = PartnerAccountDetail(
    partnerDetail = partner?.toPartnerDetailDomain(),
    partnerFeatures = features.map { it.toPartnerFeature() }
)

fun PartnerFeaturePair.toPartnerFeature(): PartnerFeature = PartnerFeature(
    id = featureId,
    featureId = featureId,
    icon = icon,
    name = name,
    status = status ?: 0,
    statusName = statusName
)

fun com.nusatim.sapiriku.api.model.PartnerDetail.toPartnerDetailDomain(): com.nusatim.sapiriku.domain.model.PartnerDetail = com.nusatim.sapiriku.domain.model.PartnerDetail(
    id = id,
    accountId = accountId,
    ownershipId = ownershipId ?: 0,
    ownershipName = ownershipName,
    companyName = companyName,
    taxNumber = taxNumber,
    imgProfile = imgProfile,
    regenciesId = regenciesId ?: 0,
    regenciesName = regenciesName,
    address = address,
    latitude = latitude ?: 0.0,
    longitude = longitude ?: 0.0,
    description = description
)

fun RewardScope.toBasicData(): BasicData = BasicData(
    id = id,
    name = name.orEmpty()
)

fun com.nusatim.sapiriku.api.model.HomeData.toHomeDataDomain(): com.nusatim.sapiriku.domain.model.HomeData = com.nusatim.sapiriku.domain.model.HomeData(
    balance = balance?.toBalance(),
    referralCode = referalCode,
    vehiclesRecommendation = vehiclesRecommendation.map { it.toVehicle() },
    promoteVehiclesRecommendation = promoteVehiclesRecommendation.map { it.toVehicle() },
    newsPreview = newsPreview.map { it.toNews() }
)

fun BalanceData.toBalance(): Balance = Balance(
    balance = balance,
    point = point
)

fun RecommendedVehicleItem.toVehicle(): Vehicle = Vehicle(
    id = id,
    title = title,
    img = img,
    price = price,
    rating = rating ?: 0.0,
    totalReview = totalReview ?: 0
)

fun NewsItem.toNews(): News = News(
    id = id,
    userType = userType ?: 0,
    title = title,
    img = img,
    content = content,
    isVoucher = isVoucher,
    voucherId = voucherId ?: 0,
    dateAdded = dateAdded
)

fun NewsPreviewItem.toNews(): News = News(
    id = id,
    title = title,
    img = img
)

fun VoucherItem.toVoucher(): Voucher = Voucher(
    id = id,
    userType = userType ?: 0,
    code = code,
    value = value,
    description = description,
    useExpire = useExpire,
    startDate = startDate,
    endDate = endDate,
    useQuota = useQuota,
    quota = quota,
    status = status
)

fun TopupConfigData.toRequestTopupConfig(): RequestTopupConfig = RequestTopupConfig(
    topupMinimum = topupMinimum,
    banks = banks.map { it.toCompanyBankDomain() }
)

fun com.nusatim.sapiriku.api.model.CompanyBank.toCompanyBankDomain(): com.nusatim.sapiriku.domain.model.CompanyBank = com.nusatim.sapiriku.domain.model.CompanyBank(
    id = id,
    icon = icon,
    bankId = bankId ?: 0,
    bankName = bankName,
    code = code,
    bankNumber = bankNumber,
    name = name
)

fun WithdrawConfigData.toRequestWithdrawConfig(): RequestWithdrawConfig = RequestWithdrawConfig(
    withdrawMinimum = withdrawMinimum,
    banks = banks.map { it.toCustomerBankDomain() }
)

fun com.nusatim.sapiriku.api.model.CustomerBank.toCustomerBankDomain(): com.nusatim.sapiriku.domain.model.CustomerBank = com.nusatim.sapiriku.domain.model.CustomerBank(
    id = id,
    accountId = accountId ?: 0,
    icon = icon,
    bankId = bankId ?: 0,
    bankName = bankName,
    code = code,
    bankNumber = bankNumber,
    name = name
)

fun PointExchangeConfigData.toExchangePointConfig(): ExchangePointConfig = ExchangePointConfig(
    exchangePointMinimum = exchangePointMinimum,
    ratePointToBalance = ratePointToBalance
)

fun PointTransactionItem.toTransactionPoint(): TransactionPoint = TransactionPoint(
    id = id,
    targetId = targetId ?: 0,
    pointDebit = pointDebit ?: 0,
    pointCredit = pointCredit ?: 0,
    description = description,
    date = dateAdded
)

fun TopupItem.toTopup(): Topup = Topup(
    id = id,
    companyBankId = companyBankId ?: 0,
    bankName = bankName,
    bankCode = bankCode,
    icon = icon,
    bankNumber = bankNumber,
    name = name,
    value = value,
    valueWithCode = valueWithCode,
    date = dateAdded,
    status = status ?: 0,
    statusName = statusName
)

fun WithdrawItem.toWithdraw(): Withdraw = Withdraw(
    id = id,
    bankId = accountBankId ?: 0,
    bankName = bankName,
    bankCode = bankCode,
    icon = icon,
    bankNumber = bankNumber,
    name = name,
    description = description,
    value = value,
    date = dateAdded,
    status = status ?: 0,
    statusName = statusName
)

fun BankOption.toBank(): Bank = Bank(
    id = id,
    name = name.orEmpty(),
    code = code.orEmpty(),
    icon = icon
)

fun BookingListItem.toRentVehicleTransaction(): RentVehicleTransaction = RentVehicleTransaction(
    id = id,
    vehicleTitle = vehicleTitle,
    img = img,
    pricePackageName = pricePackageName,
    startDate = startDate,
    endDate = endDate,
    totalPayment = totalPayment,
    statusName = statusName,
    dateModified = dateModified
)

fun com.nusatim.sapiriku.api.model.VehicleDetail.toVehicleDomain(): com.nusatim.sapiriku.domain.model.Vehicle = com.nusatim.sapiriku.domain.model.Vehicle(
    id = id,
    functionalType = functionalType ?: -1,
    vehicleType = vehicleType ?: -1,
    title = title,
    brandId = brandId ?: -1,
    vehicleModel = vehicleModel ?: -1,
    year = year ?: 0,
    colorId = colorId ?: -1,
    maxPassenger = maxPassenger ?: 0,
    maxBaggage = maxBaggage,
    drivenType = drivenType ?: -1,
    transmitionType = transmitionType ?: -1,
    fuelType = fuelType ?: -1,
    price = price,
    withDriver = withDriver ?: 0,
    priceWithDriverBasic = priceWithDriverBasic,
    priceWithDriverFull = priceWithDriverFull,
    delivered = delivered,
    pickoff = pickoff,
    status = status,
    vehicleTypeName = vehicleTypeName,
    vehicleTypeIcon = vehicleTypeIcon,
    brandName = brandName,
    brandIcon = brandIcon,
    vehicleModelName = vehicleModelName,
    colorName = colorName,
    colorValue = colorValue
)

fun TransactionDetail.toRentVehicleTransactionDetail(): RentVehicleTransactionDetail = RentVehicleTransactionDetail(
    id = id,
    pricePackage = pricePackage ?: 0,
    pricePackageName = pricePackageName,
    price = price,
    startDate = startDate,
    endDate = endDate,
    delivery = delivery,
    deliveryDate = deliveryDate,
    deliveryAddress = deliveryAddress,
    deliveryLatitude = deliveryLatitude ?: 0.0,
    deliveryLongitude = deliveryLongitude ?: 0.0,
    deliveryFee = deliveryFee,
    pickoff = pickoff,
    pickoffDate = pickoffDate,
    pickoffAddress = pickoffAddress,
    pickoffLatitude = pickoffLatitude ?: 0.0,
    pickoffLongitude = pickoffLongitude ?: 0.0,
    pickoffFee = pickoffFee,
    discount = discount,
    totalPayment = totalPayment,
    cashOnDelivery = cashOnDelivery,
    overtime = overtime,
    overtimeHour = overtimeHour,
    overtimeFee = overtimeFee,
    totalOvertimeFee = totalOvertimeFee,
    adminFee = adminFee,
    status = status ?: 0,
    statusName = statusName,
    dateModified = dateModified
)

fun PartnerInfo.toPartnerDetailDomain(): com.nusatim.sapiriku.domain.model.PartnerDetail = com.nusatim.sapiriku.domain.model.PartnerDetail(
    id = id,
    accountId = accountId ?: 0,
    companyName = companyName,
    imgProfile = imgProfile,
    description = description,
    regenciesName = regenciesName,
    ownershipName = ownershipName,
    address = address,
    latitude = latitude ?: 0.0,
    longitude = longitude ?: 0.0
)

fun CustomerInfo.toCustomerDetailDomain(): com.nusatim.sapiriku.domain.model.CustomerDetail = com.nusatim.sapiriku.domain.model.CustomerDetail(
    id = id,
    firstName = firstName,
    lastName = lastName,
    phone = phone,
    identityNumber = identityNumber,
    imgProfile = imgProfile,
    imgIdentity = imgIdentity
)

fun BookingDetailData.toRentVehicleDetail(): RentVehicleDetail = RentVehicleDetail(
    customerDetail = customer?.toCustomerDetailDomain(),
    partnerDetail = partner?.toPartnerDetailDomain(),
    vehicle = vehicle.toVehicleDomain(),
    voucher = voucher?.toVoucher(),
    rentVehicleTransactionDetail = transactionDetail.toRentVehicleTransactionDetail(),
    balance = balance?.toBalance(),
    hourOvertime = hourOvertime,
    feedback = feedback
)

fun PartnerRewardItem.toReward(): Reward = Reward(
    id = id,
    title = title,
    img = img,
    rewardType = rewardType ?: 0,
    target = target,
    pointReward = pointReward ?: 0,
    aquired = acquired,
    processed = processed ?: 0,
    claimed = claimed ?: 0,
    rewardId = historyRewardId ?: 0
)

fun FeatureRewardProgress.toPartnerReward(): PartnerReward = PartnerReward(
    featureName = featureName,
    rewards = rewards.map { it.toReward() }
)

fun PromoteItem.toPromoteVehicle(): PromoteVehicle = PromoteVehicle(
    id = id,
    itemId = itemId ?: 0,
    img = img,
    title = title,
    startDate = startDate,
    endDate = endDate,
    pricePerDay = pricePerDay,
    days = days,
    totalPayment = totalPayment,
    canceledTotalReturn = canceledTotalReturn,
    viewer = viewer,
    status = status ?: 0,
    statusName = statusName,
    dateAdded = dateAdded
)

fun PromotionInputConfigData.toInputPromoteRentVehicleConfig(): InputPromoteRentVehicleConfig = InputPromoteRentVehicleConfig(
    info = info,
    pricePerDay = pricePerDay,
    vehicles = vehicles.map { it.toVehicle() }
)

fun VehicleListItem.toVehicle(): Vehicle = Vehicle(
    id = id,
    title = title,
    functionalType = functionalType ?: -1,
    vehicleTypeName = vehicleTypeName,
    withDriver = withDriver,
    maxPassenger = maxPassenger ?: 0,
    colorName = colorName,
    colorValue = colorValue,
    price = price,
    priceWithDriverBasic = priceWithDriverBasic,
    priceWithDriverFull = priceWithDriverFull,
    img = img,
    rating = rating ?: 0.0,
    totalReview = totalReview ?: 0
)

fun PartnerRentConfig.toRentVehicleConfig(): RentVehicleConfig = RentVehicleConfig(
    forceWithDriver = forceWithDriver,
    forceDisableDelivery = forceDisableDelivery,
    forceDisablePickoff = forceDisablePickoff,
    deliveryFee = deliveryFee,
    pickoffFee = pickoffFee,
    overtimeFee = overtimeFee,
    maxDayCod = maxDayCod
)

fun FunctionalTypeItem.toBasicData(): BasicData = BasicData(
    id = id,
    name = name.orEmpty()
)

fun VehicleTypeOption.toBasicData(): BasicData = BasicData(
    id = id,
    name = name.orEmpty()
)

fun BrandOption.toBasicData(): BasicData = BasicData(
    id = id,
    name = name.orEmpty()
)

fun ColorOption.toBasicData(): BasicData = BasicData(
    id = id,
    name = name.orEmpty()
)

fun TransmitionTypeOption.toBasicData(): BasicData = BasicData(
    id = id,
    name = name.orEmpty()
)

fun DrivenTypeOption.toBasicData(): BasicData = BasicData(
    id = id,
    name = name.orEmpty()
)

fun FuelOption.toBasicData(): BasicData = BasicData(
    id = id,
    name = name.orEmpty()
)

fun VehicleModelOption.toBasicData(): BasicData = BasicData(
    id = id,
    name = name.orEmpty()
)
