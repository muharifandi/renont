package com.rentone.user.domain.usecase

import com.rentone.user.domain.model.command.UploadImageCommand
import com.rentone.user.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class PostVerificationTopupUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke(topupId: Int, command: UploadImageCommand) =
        customerFinanceRepository.postVerificationTopup(topupId, command)
}
