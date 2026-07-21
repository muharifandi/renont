package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.model.command.UploadImageCommand
import com.nusatim.sapiriku.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class PostVerificationTopupUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke(topupId: Int, command: UploadImageCommand) =
        customerFinanceRepository.postVerificationTopup(topupId, command)
}
