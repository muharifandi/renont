package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class PostPartnerVehicleUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    operator fun invoke(form: Map<String, String>, photos: List<String>) =
        partnerVehicleRepository.postVehicle(form, photos)
}
