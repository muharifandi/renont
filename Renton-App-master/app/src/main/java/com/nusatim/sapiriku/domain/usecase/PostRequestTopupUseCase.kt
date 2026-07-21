package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.model.command.TopupRequestCommand
import com.nusatim.sapiriku.domain.repository.CustomerFinanceRepository
import javax.inject.Inject

class PostRequestTopupUseCase @Inject constructor(
    private val customerFinanceRepository: CustomerFinanceRepository
) {
    operator fun invoke(command: TopupRequestCommand) =
        customerFinanceRepository.postRequestTopup(command)
}
