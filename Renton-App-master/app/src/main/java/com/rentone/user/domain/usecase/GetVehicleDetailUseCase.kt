package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.VehicleRepository
import javax.inject.Inject

class GetVehicleDetailUseCase @Inject constructor(
    private val vehicleRepository: VehicleRepository
) {
    operator fun invoke(id: Int) = vehicleRepository.getVehicleDetail(id)
}
