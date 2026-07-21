package com.rentone.user.domain.usecase

import com.rentone.user.domain.model.command.UploadImageCommand
import com.rentone.user.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class UploadPartnerVehicleImageUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    suspend operator fun invoke(command: UploadImageCommand) =
        partnerVehicleRepository.uploadVehicleImage(command)
}
