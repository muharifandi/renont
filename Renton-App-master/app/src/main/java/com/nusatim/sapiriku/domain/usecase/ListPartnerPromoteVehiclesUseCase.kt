package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.model.FilterList
import com.nusatim.sapiriku.domain.repository.PartnerPromotionRepository
import javax.inject.Inject

class ListPartnerPromoteVehiclesUseCase @Inject constructor(
    private val partnerPromotionRepository: PartnerPromotionRepository
) {
    suspend operator fun invoke(page: Int, pageSize: Int, sortIndex: Int, filterList: FilterList) =
        partnerPromotionRepository.listPromoteVehicles(page, pageSize, sortIndex, filterList)
}
