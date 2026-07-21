package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class ListCustomerTopupUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    suspend operator fun invoke(page: Int, pageSize: Int) =
        customerFinanceRepository.listTopups(page, pageSize)
}
