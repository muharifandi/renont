package com.rentone.user.domain.usecase

import com.rentone.user.domain.model.command.TopupRequestCommand
import com.rentone.user.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class PostRequestTopupUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke(command: TopupRequestCommand) =
        customerFinanceRepository.postRequestTopup(command)
}
