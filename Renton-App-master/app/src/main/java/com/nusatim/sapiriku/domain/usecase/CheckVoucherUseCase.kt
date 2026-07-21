package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.VehicleRepository
import javax.inject.Inject

class CheckVoucherUseCase @Inject constructor(
    private val vehicleRepository: VehicleRepository
) {
    operator fun invoke(code: String, startDate: String?) = vehicleRepository.checkVoucher(code, startDate)
}
