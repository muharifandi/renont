package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.CustomerTransactionRepository
import javax.inject.Inject

class ListCustomerRentVehicleTransactionsUseCase @Inject constructor(
    private val customerTransactionRepository: CustomerTransactionRepository
) {
    suspend operator fun invoke(page: Int, pageSize: Int, status: Int) =
        customerTransactionRepository.listTransactions(page, pageSize, status)
}
