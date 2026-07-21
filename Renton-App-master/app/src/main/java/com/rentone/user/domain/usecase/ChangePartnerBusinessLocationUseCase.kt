package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class ChangePartnerBusinessLocationUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(latitude: Double, longitude: Double) =
        partnerProfileRepository.changeBusinessLocation(latitude, longitude)
}
