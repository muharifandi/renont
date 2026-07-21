package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.VehicleRepository
import javax.inject.Inject

class GetCheckoutDetailUseCase @Inject constructor(
    private val vehicleRepository: VehicleRepository
) {
    operator fun invoke(vehicleId: Int, pricePackage: Int, startDate: String?, endDate: String?) =
        vehicleRepository.getCheckoutDetail(vehicleId, pricePackage, startDate, endDate)
}
