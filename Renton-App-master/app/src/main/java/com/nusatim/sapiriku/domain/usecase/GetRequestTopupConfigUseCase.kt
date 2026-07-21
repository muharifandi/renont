package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class GetRequestTopupConfigUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke() = customerFinanceRepository.getRequestTopupConfig()
}
