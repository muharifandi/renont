package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class UpdatePartnerRentVehicleConfigUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    operator fun invoke(form: Map<String, String>) =
        partnerVehicleRepository.updateConfig(form)
}
