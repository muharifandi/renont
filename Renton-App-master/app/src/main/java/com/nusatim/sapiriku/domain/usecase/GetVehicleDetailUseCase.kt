package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.VehicleRepository
import javax.inject.Inject

class GetVehicleDetailUseCase @Inject constructor(
    private val vehicleRepository: VehicleRepository
) {
    operator fun invoke(id: Int) = vehicleRepository.getVehicleDetail(id)
}
