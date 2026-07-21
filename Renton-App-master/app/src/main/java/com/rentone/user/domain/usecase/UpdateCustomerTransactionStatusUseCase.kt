package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.CustomerTransactionRepository
import javax.inject.Inject

class UpdateCustomerTransactionStatusUseCase @Inject constructor(
    private val customerTransactionRepository: CustomerTransactionRepository
) {
    operator fun invoke(id: Int, status: Int) = customerTransactionRepository.updateTransactionStatus(id, status)
}
