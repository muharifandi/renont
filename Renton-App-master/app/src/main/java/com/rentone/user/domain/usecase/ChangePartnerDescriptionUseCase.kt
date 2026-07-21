package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class ChangePartnerDescriptionUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(description: String) =
        partnerProfileRepository.changeDescription(description)
}
