package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerTransactionRepository
import javax.inject.Inject

class GetPartnerTransactionDetailUseCase @Inject constructor(
    private val partnerTransactionRepository: PartnerTransactionRepository
) {
    operator fun invoke(id: Int) =
        partnerTransactionRepository.getTransactionDetail(id)
}
