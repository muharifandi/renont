package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerTransactionRepository
import javax.inject.Inject

class GetCustomerTransactionDetailUseCase @Inject constructor(
    private val customerTransactionRepository: CustomerTransactionRepository
) {
    operator fun invoke(id: Int) = customerTransactionRepository.getTransactionDetail(id)
}
