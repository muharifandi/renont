package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class GetCustomerBankDetailUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke(id: Int) = customerFinanceRepository.getBankDetail(id)
}
