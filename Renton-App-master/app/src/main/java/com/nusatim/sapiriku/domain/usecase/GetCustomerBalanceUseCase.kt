package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class GetCustomerBalanceUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke() = customerFinanceRepository.getBalance()
}
