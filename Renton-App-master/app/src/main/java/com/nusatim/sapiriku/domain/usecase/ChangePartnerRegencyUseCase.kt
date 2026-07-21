package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class ChangePartnerRegencyUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(regenciesId: Int) =
        partnerProfileRepository.changeRegency(regenciesId)
}
