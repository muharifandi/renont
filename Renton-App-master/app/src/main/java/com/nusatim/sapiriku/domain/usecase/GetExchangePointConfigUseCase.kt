package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class GetExchangePointConfigUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke() = customerFinanceRepository.getExchangePointConfig()
}
