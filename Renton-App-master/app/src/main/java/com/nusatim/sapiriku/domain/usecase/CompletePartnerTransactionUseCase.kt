package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerTransactionRepository
import javax.inject.Inject

class CompletePartnerTransactionUseCase @Inject constructor(
    private val partnerTransactionRepository: PartnerTransactionRepository
) {
    operator fun invoke(id: Int) =
        partnerTransactionRepository.completeTransaction(id)
}
