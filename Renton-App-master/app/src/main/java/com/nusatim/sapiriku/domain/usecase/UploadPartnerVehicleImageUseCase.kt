package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import com.nusatim.sapiriku.domain.repository.PartnerVehicleRepository
import javax.inject.Inject

class UploadPartnerVehicleImageUseCase @Inject constructor(
    private val partnerVehicleRepository: PartnerVehicleRepository
) {
    suspend operator fun invoke(command: UploadImageCommand) =
        partnerVehicleRepository.uploadVehicleImage(command)
}
