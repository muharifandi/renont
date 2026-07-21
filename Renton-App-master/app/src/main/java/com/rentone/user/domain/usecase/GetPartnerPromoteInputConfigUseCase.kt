package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerPromotionRepository
import javax.inject.Inject

class GetPartnerPromoteInputConfigUseCase @Inject constructor(
    private val partnerPromotionRepository: PartnerPromotionRepository
) {
    operator fun invoke() = partnerPromotionRepository.getPromoteInputConfig()
}
