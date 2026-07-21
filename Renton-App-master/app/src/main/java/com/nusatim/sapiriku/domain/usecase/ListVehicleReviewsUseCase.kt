package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.VehicleRepository
import javax.inject.Inject

class ListVehicleReviewsUseCase @Inject constructor(
    private val vehicleRepository: VehicleRepository
) {
    suspend operator fun invoke(vehicleId: Int, page: Int, pageSize: Int) =
        vehicleRepository.listVehicleReviews(vehicleId, page, pageSize)
}
