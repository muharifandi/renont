package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.CustomerTransactionRepository
import javax.inject.Inject

class CancelCustomerTransactionUseCase @Inject constructor(
    private val customerTransactionRepository: CustomerTransactionRepository
) {
    operator fun invoke(id: Int) = customerTransactionRepository.cancelTransaction(id)
}
