package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class GetCustomerPointUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke() = customerFinanceRepository.getPoint()
}
