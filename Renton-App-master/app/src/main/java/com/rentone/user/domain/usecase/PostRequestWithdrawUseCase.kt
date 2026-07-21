package com.rentone.user.domain.usecase

import com.rentone.user.domain.model.command.WithdrawRequestCommand
import com.rentone.user.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class PostRequestWithdrawUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke(command: WithdrawRequestCommand) =
        customerFinanceRepository.postRequestWithdraw(command)
}
