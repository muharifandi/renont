package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerPromotionRepository
import javax.inject.Inject

class CancelPartnerPromoteUseCase @Inject constructor(
    private val partnerPromotionRepository: PartnerPromotionRepository
) {
    operator fun invoke(id: Int) =
        partnerPromotionRepository.cancelPromote(id)
}
