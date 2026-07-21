package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerTransactionRepository
import javax.inject.Inject

class UpdateCustomerTransactionStatusUseCase @Inject constructor(
    private val customerTransactionRepository: CustomerTransactionRepository
) {
    operator fun invoke(id: Int, status: Int) = customerTransactionRepository.updateTransactionStatus(id, status)
}
