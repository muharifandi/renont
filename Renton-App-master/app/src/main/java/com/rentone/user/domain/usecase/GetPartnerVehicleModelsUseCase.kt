package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class GetPartnerVehicleModelsUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    operator fun invoke(brandId: Int) =
        partnerVehicleRepository.getInputVehicleModel(brandId)
}
