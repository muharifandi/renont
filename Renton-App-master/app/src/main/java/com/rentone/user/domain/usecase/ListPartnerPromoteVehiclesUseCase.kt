package com.rentone.user.domain.usecase

import com.rentone.user.domain.model.FilterList
import com.rentone.user.domain.repository.PartnerPromotionRepository
import javax.inject.Inject

class ListPartnerPromoteVehiclesUseCase @Inject constructor(
    private val partnerPromotionRepository: PartnerPromotionRepository
) {
    suspend operator fun invoke(page: Int, pageSize: Int, sortIndex: Int, filterList: FilterList) =
        partnerPromotionRepository.listPromoteVehicles(page, pageSize, sortIndex, filterList)
}
