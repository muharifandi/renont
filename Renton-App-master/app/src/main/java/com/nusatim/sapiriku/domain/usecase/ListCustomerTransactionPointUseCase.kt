package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class ListCustomerTransactionPointUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    suspend operator fun invoke(page: Int, pageSize: Int) =
        customerFinanceRepository.listTransactionPoints(page, pageSize)
}
