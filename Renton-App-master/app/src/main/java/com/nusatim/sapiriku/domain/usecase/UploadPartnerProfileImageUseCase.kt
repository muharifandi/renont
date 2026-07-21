package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import com.nusatim.sapiriku.domain.repository.PartnerProfileRepository
import javax.inject.Inject

class UploadPartnerProfileImageUseCase @Inject constructor(
    private val partnerProfileRepository: PartnerProfileRepository
) {
    operator fun invoke(command: UploadImageCommand) = partnerProfileRepository.uploadProfileImage(command)
}
