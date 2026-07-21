package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class ListCustomerTransactionPointUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    suspend operator fun invoke(page: Int, pageSize: Int) =
        customerFinanceRepository.listTransactionPoints(page, pageSize)
}
