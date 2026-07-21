package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class GetPartnerVehicleDetailUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    operator fun invoke(id: Int) =
        partnerVehicleRepository.getVehicleDetail(id)
}
