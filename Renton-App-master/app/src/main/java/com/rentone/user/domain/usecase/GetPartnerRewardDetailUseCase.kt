package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerRewardRepository
import javax.inject.Inject

class GetPartnerRewardDetailUseCase @Inject constructor(
    private val partnerRewardRepository: PartnerRewardRepository
) {
    operator fun invoke(rewardScope: Int) = partnerRewardRepository.getRewardDetail(rewardScope)
}
