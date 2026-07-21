package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class ChangePartnerCompanyNameUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(companyName: String) =
        partnerProfileRepository.changeCompanyName(companyName)
}
