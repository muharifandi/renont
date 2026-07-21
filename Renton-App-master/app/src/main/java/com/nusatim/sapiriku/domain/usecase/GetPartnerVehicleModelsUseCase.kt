package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class GetPartnerVehicleModelsUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    operator fun invoke(brandId: Int) =
        partnerVehicleRepository.getInputVehicleModel(brandId)
}
