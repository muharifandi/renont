package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerTransactionRepository
import javax.inject.Inject

class CancelPartnerTransactionUseCase @Inject constructor(
    private val partnerTransactionRepository: PartnerTransactionRepository
) {
    operator fun invoke(id: Int) =
        partnerTransactionRepository.cancelTransaction(id)
}
