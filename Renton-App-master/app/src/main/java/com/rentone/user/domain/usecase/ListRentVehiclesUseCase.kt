package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.VehicleRepository
import javax.inject.Inject

class ListRentVehiclesUseCase @Inject constructor(
    private val vehicleRepository: VehicleRepository
) {
    suspend operator fun invoke(param: Map<String, String>) = vehicleRepository.listVehicles(param)
}
