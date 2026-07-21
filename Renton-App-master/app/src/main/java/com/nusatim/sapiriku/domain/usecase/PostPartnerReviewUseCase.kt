package com.nusatim.sapiriku.domain.usecase

import com.nusatim.sapiriku.domain.repository.PartnerTransactionRepository
import javax.inject.Inject

class PostPartnerReviewUseCase @Inject constructor(
    private val partnerTransactionRepository: PartnerTransactionRepository
) {
    operator fun invoke(transactionId: Int, rating: Float, comment: String) =
        partnerTransactionRepository.postReview(transactionId, rating, comment)
}
