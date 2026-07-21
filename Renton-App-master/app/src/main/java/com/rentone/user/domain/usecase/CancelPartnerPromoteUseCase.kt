package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerPromotionRepository
import javax.inject.Inject

class CancelPartnerPromoteUseCase @Inject constructor(
    private val partnerPromotionRepository: PartnerPromotionRepository
) {
    operator fun invoke(id: Int) =
        partnerPromotionRepository.cancelPromote(id)
}
