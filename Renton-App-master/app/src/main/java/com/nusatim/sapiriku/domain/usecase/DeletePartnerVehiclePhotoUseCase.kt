package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class DeletePartnerVehiclePhotoUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    suspend operator fun invoke(id: Int) =
        partnerVehicleRepository.deleteVehiclePhoto(id)
}
