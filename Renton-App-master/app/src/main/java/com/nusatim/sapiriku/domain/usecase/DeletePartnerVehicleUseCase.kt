package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class DeletePartnerVehicleUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    operator fun invoke(id: Int) =
        partnerVehicleRepository.deleteVehicle(id)
}
