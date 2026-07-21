package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerTransactionRepository
import javax.inject.Inject

class ListCustomerRentVehicleTransactionsUseCase @Inject constructor(
    private val customerTransactionRepository: CustomerTransactionRepository
) {
    suspend operator fun invoke(page: Int, pageSize: Int, status: Int) =
        customerTransactionRepository.listTransactions(page, pageSize, status)
}
