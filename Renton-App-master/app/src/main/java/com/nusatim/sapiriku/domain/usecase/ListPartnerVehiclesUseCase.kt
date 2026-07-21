package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class ListPartnerVehiclesUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    suspend operator fun invoke(param: Map<String, String>) =
        partnerVehicleRepository.listVehicles(param)
}
