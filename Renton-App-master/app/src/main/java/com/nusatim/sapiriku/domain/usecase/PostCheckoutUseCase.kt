package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.model.command.CheckoutCommand
import com.nusatim.sapiriku.domain.repository.VehicleRepository
import javax.inject.Inject

class PostCheckoutUseCase @Inject constructor(
    private val vehicleRepository: VehicleRepository
) {
    operator fun invoke(command: CheckoutCommand) = vehicleRepository.postCheckout(command)
}
