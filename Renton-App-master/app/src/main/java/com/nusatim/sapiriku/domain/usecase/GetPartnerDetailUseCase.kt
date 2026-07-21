package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class GetPartnerDetailUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke() = partnerProfileRepository.getDetail()
}
