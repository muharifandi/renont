package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.core.common.Resource
import com.nusatim.sapiriku.domain.model.Vehicle
import com.nusatim.sapiriku.domain.repository.VehicleRepository
import kotlinx.coroutines.flow.Flow
import javax.inject.Inject

class GetRecommendationVehiclesUseCase @Inject constructor(
    private val vehicleRepository: VehicleRepository
) {
    operator fun invoke(): Flow<Resource<List<Vehicle>>> {
        return vehicleRepository.getRecommendationVehicles()
    }
}
