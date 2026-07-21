package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerPromotionRepository
import javax.inject.Inject

class PostPartnerPromoteUseCase @Inject constructor(
    private val partnerPromotionRepository: PartnerPromotionRepository
) {
    operator fun invoke(itemId: Int, startDate: String, endDate: String) =
        partnerPromotionRepository.postPromote(itemId, startDate, endDate)
}
