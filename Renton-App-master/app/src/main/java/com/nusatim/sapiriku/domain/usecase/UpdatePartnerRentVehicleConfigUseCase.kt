package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class UpdatePartnerRentVehicleConfigUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    operator fun invoke(form: Map<String, String>) =
        partnerVehicleRepository.updateConfig(form)
}
