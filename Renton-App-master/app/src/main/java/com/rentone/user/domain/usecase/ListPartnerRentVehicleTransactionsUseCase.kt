package com.rentone.user.domain.usecase

import com.rentone.user.domain.repository.PartnerTransactionRepository
import javax.inject.Inject

class ListPartnerRentVehicleTransactionsUseCase @Inject constructor(
    private val partnerTransactionRepository: PartnerTransactionRepository
) {
    suspend operator fun invoke(page: Int, pageSize: Int, status: Int) =
        partnerTransactionRepository.listTransactions(page, pageSize, status)
}
