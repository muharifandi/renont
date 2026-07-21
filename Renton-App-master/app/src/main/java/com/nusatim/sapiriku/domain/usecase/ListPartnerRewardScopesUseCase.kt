package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerRewardRepository
import javax.inject.Inject

class ListPartnerRewardScopesUseCase @Inject constructor(
    private val partnerRewardRepository: PartnerRewardRepository
) {
    operator fun invoke() = partnerRewardRepository.listRewardScopes()
}
