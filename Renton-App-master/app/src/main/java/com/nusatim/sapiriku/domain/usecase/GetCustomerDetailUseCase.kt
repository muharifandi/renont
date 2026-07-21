package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerAccountRepository
import javax.inject.Inject

class GetCustomerDetailUseCase @Inject constructor(
    private val customerAccountRepository: CustomerAccountRepository
) {
    operator fun invoke() = customerAccountRepository.getDetail()
}
