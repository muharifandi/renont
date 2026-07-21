package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class ChangePartnerCompanyNameUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(companyName: String) =
        partnerProfileRepository.changeCompanyName(companyName)
}
