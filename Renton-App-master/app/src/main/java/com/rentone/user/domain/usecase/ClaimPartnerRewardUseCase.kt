package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerRewardRepository
import javax.inject.Inject

class ClaimPartnerRewardUseCase @Inject constructor(
    private val partnerRewardRepository: PartnerRewardRepository
) {
    operator fun invoke(rewardId: Int) = partnerRewardRepository.claimReward(rewardId)
}
