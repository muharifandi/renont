package com.nusatim.sapiriku.domain.model

import kotlinx.serialization.Serializable
@Serializable
data class PartnerAccountDetail(
    val partnerDetail: PartnerDetail?,
    val partnerFeatures: List<PartnerFeature>
)
