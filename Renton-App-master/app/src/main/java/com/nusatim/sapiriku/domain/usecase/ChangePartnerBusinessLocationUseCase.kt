package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class ChangePartnerBusinessLocationUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(latitude: Double, longitude: Double) =
        partnerProfileRepository.changeBusinessLocation(latitude, longitude)
}
