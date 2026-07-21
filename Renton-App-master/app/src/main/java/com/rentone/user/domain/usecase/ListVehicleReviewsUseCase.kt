package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.VehicleRepository
import javax.inject.Inject

class ListVehicleReviewsUseCase @Inject constructor(
    private val vehicleRepository: VehicleRepository
) {
    suspend operator fun invoke(vehicleId: Int, page: Int, pageSize: Int) =
        vehicleRepository.listVehicleReviews(vehicleId, page, pageSize)
}
