package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerFeatureRepository
import javax.inject.Inject

class RequestPartnerFeatureUseCase @Inject constructor(
    private val partnerFeatureRepository: PartnerFeatureRepository
) {
    operator fun invoke(featureId: Int) =
        partnerFeatureRepository.requestFeature(featureId)
}
