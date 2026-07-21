package com.rentone.user.domain.usecase

import com.rentone.user.domain.model.command.AddBankCommand
import com.rentone.user.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class PostCustomerBankUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke(command: AddBankCommand) =
        customerFinanceRepository.postBank(command)
}
