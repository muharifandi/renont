package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerFeatureRepository
import javax.inject.Inject

class RequestPartnerFeatureUseCase @Inject constructor(
    private val partnerFeatureRepository: PartnerFeatureRepository
) {
    operator fun invoke(featureId: Int) =
        partnerFeatureRepository.requestFeature(featureId)
}
