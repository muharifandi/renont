package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.model.command.AddBankCommand
import com.nusatim.sapiriku.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class PostCustomerBankUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke(command: AddBankCommand) =
        customerFinanceRepository.postBank(command)
}
