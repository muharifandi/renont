package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerRewardRepository
import javax.inject.Inject

class GetPartnerRewardDetailUseCase @Inject constructor(
    private val partnerRewardRepository: PartnerRewardRepository
) {
    operator fun invoke(rewardScope: Int) = partnerRewardRepository.getRewardDetail(rewardScope)
}
