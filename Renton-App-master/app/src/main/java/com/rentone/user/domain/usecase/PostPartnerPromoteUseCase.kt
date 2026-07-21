package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerPromotionRepository
import javax.inject.Inject

class PostPartnerPromoteUseCase @Inject constructor(
    private val partnerPromotionRepository: PartnerPromotionRepository
) {
    operator fun invoke(itemId: Int, startDate: String, endDate: String) =
        partnerPromotionRepository.postPromote(itemId, startDate, endDate)
}
