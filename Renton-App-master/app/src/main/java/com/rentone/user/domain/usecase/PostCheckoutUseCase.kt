package com.rentone.user.domain.usecase

import com.rentone.user.domain.model.command.CheckoutCommand
import com.rentone.user.domain.repository.VehicleRepository
import javax.inject.Inject

class PostCheckoutUseCase @Inject constructor(
    private val vehicleRepository: VehicleRepository
) {
    operator fun invoke(command: CheckoutCommand) = vehicleRepository.postCheckout(command)
}
