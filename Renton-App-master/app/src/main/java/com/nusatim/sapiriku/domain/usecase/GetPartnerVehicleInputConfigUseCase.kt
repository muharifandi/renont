package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class GetPartnerVehicleInputConfigUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    operator fun invoke(functionalType: Int) =
        partnerVehicleRepository.getInputConfig(functionalType)
}
