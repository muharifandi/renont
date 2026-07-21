package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerTransactionRepository
import javax.inject.Inject

class ListPartnerRentVehicleTransactionsUseCase @Inject constructor(
    private val partnerTransactionRepository: PartnerTransactionRepository
) {
    suspend operator fun invoke(page: Int, pageSize: Int, status: Int) =
        partnerTransactionRepository.listTransactions(page, pageSize, status)
}
