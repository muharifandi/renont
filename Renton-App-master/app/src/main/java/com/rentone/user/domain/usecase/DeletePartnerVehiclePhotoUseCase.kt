package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class DeletePartnerVehiclePhotoUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    suspend operator fun invoke(id: Int) =
        partnerVehicleRepository.deleteVehiclePhoto(id)
}
