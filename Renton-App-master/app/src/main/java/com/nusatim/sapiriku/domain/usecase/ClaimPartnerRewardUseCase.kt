package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerRewardRepository
import javax.inject.Inject

class ClaimPartnerRewardUseCase @Inject constructor(
    private val partnerRewardRepository: PartnerRewardRepository
) {
    operator fun invoke(rewardId: Int) = partnerRewardRepository.claimReward(rewardId)
}
