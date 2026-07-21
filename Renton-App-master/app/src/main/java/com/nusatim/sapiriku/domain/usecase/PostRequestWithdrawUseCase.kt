package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.model.command.WithdrawRequestCommand
import com.nusatim.sapiriku.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class PostRequestWithdrawUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke(command: WithdrawRequestCommand) =
        customerFinanceRepository.postRequestWithdraw(command)
}
