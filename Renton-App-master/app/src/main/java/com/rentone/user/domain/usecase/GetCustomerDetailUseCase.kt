package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.CustomerAccountRepository
import javax.inject.Inject

class GetCustomerDetailUseCase @Inject constructor(
    private val customerAccountRepository: CustomerAccountRepository
) {
    operator fun invoke() = customerAccountRepository.getDetail()
}
