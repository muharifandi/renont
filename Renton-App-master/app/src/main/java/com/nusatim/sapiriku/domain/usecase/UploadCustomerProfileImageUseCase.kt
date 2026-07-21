package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import com.nusatim.sapiriku.domain.repository.CustomerAccountRepository
import javax.inject.Inject

class UploadCustomerProfileImageUseCase @Inject constructor(
    private val customerAccountRepository: CustomerAccountRepository
) {
    operator fun invoke(command: UploadImageCommand) =
        customerAccountRepository.uploadProfileImage(command)
}
