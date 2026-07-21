package com.rentone.user.domain.usecase

import com.rentone.user.domain.model.command.UploadImageCommand
import com.rentone.user.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class UploadPartnerProfileImageUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(command: UploadImageCommand) = partnerProfileRepository.uploadProfileImage(command)
}
