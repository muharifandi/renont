package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerTransactionRepository
import javax.inject.Inject

class UpdatePartnerTransactionStatusUseCase @Inject constructor(
    private val partnerTransactionRepository: PartnerTransactionRepository
) {
    operator fun invoke(id: Int, status: Int) =
        partnerTransactionRepository.updateTransactionStatus(id, status)
}
