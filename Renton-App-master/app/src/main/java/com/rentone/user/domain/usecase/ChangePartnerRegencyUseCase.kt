package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class ChangePartnerRegencyUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(regenciesId: Int) =
        partnerProfileRepository.changeRegency(regenciesId)
}
