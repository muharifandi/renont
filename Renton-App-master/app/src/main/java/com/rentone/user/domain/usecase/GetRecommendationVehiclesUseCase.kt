package com.rentone.user.domain.usecase

import com.rentone.user.core.common.Resource
import com.rentone.user.domain.model.Vehicle
import com.rentone.user.domain.repository.VehicleRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject

class GetRecommendationVehiclesUseCase @Inject constructor(
    private val vehicleRepository: VehicleRepository
) {
    operator fun invoke(): Flow<Resource<List<Vehicle>>> {
        return vehicleRepository.getRecommendationVehicles()
    }
}
