package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class GetPartnerVehicleInputConfigUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    operator fun invoke(functionalType: Int) =
        partnerVehicleRepository.getInputConfig(functionalType)
}
