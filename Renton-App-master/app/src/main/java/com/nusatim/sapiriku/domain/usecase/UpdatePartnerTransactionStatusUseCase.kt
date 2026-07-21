package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerTransactionRepository
import javax.inject.Inject

class UpdatePartnerTransactionStatusUseCase @Inject constructor(
    private val partnerTransactionRepository: PartnerTransactionRepository
) {
    operator fun invoke(id: Int, status: Int) =
        partnerTransactionRepository.updateTransactionStatus(id, status)
}
