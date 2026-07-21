package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class PostPartnerVehicleUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    operator fun invoke(form: Map<String, String>, photos: List<String>) =
        partnerVehicleRepository.postVehicle(form, photos)
}
