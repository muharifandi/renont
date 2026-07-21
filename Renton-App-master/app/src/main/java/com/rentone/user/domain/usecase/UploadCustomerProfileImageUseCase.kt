package com.rentone.user.domain.usecase

import com.rentone.user.domain.model.command.UploadImageCommand
import com.rentone.user.domain.repository.CustomerAccountRepository
import javax.inject.Inject

class UploadCustomerProfileImageUseCase @Inject constructor(
    private val customerAccountRepository: CustomerAccountRepository
) {
    operator fun invoke(command: UploadImageCommand) =
        customerAccountRepository.uploadProfileImage(command)
}
